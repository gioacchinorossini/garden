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
    $title_type = htmlspecialchars(trim($_POST['title_type'] ?? 'OCT'));
    $title_number = htmlspecialchars(trim($_POST['title_number'] ?? ''));
    $rod_name = htmlspecialchars(trim($_POST['rod_name'] ?? ''));
    $epeb_type = htmlspecialchars(trim($_POST['epeb_type'] ?? 'CCV'));
    $epeb_no = htmlspecialchars(trim($_POST['epeb_no'] ?? ''));

    // Handle OCT (Original Certificate of Title) Document Upload
    $title_doc_path = 'assets/images/sample_title_cert.svg';
    $oct_file = $_FILES['oct_document'] ?? $_FILES['title_document'] ?? null;
    if ($oct_file && $oct_file['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($oct_file['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['pdf', 'jpg', 'jpeg', 'png', 'svg'])) {
            $uploadDir = __DIR__ . '/../uploads/titles/';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0777, true);
            }
            $fileName = 'oct_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            $uploadTarget = $uploadDir . $fileName;
            if (move_uploaded_file($oct_file['tmp_name'], $uploadTarget)) {
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
        'oct_document_path' => $title_doc_path,
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
            <!-- Step-by-Step Guided Process Indicator Bar -->
            <div class="border-bottom bg-light bg-opacity-75 px-3 px-md-4 py-2 d-flex flex-wrap align-items-center justify-content-between gap-2" id="registrationStepBar">
                <div class="d-flex align-items-center gap-1 gap-sm-2 flex-wrap text-xs">
                    <!-- Step 1 Item -->
                    <div class="d-flex align-items-center gap-1.5 px-2.5 py-1 rounded-pill bg-white border border-success shadow-2xs transition-all" id="step1Item" style="cursor: pointer;" onclick="goToStep(1)" title="Step 1: Pinpoint your land location">
                        <span class="rounded-circle d-flex align-items-center justify-content-center text-white bg-success fw-bold" style="width: 20px; height: 20px; font-size: 0.7rem;" id="step1Badge">1</span>
                        <span id="step1Title" class="fw-bold text-dark">Pinpoint Location</span>
                    </div>
                    <i class="bi bi-chevron-right text-muted opacity-50" style="font-size: 0.65rem;"></i>
                    <!-- Step 2 Item -->
                    <div class="d-flex align-items-center gap-1.5 px-2.5 py-1 rounded-pill bg-transparent text-muted transition-all" id="step2Item" style="cursor: pointer;" onclick="goToStep(2)" title="Step 2: Draw your plot boundary">
                        <span class="rounded-circle d-flex align-items-center justify-content-center border bg-white text-secondary fw-bold" style="width: 20px; height: 20px; font-size: 0.7rem;" id="step2Badge">2</span>
                        <span id="step2Title">Draw Plot</span>
                    </div>
                    <i class="bi bi-chevron-right text-muted opacity-50" style="font-size: 0.65rem;"></i>
                    <!-- Step 3 Item -->
                    <div class="d-flex align-items-center gap-1.5 px-2.5 py-1 rounded-pill bg-transparent text-muted transition-all" id="step3Item" style="cursor: pointer;" onclick="goToStep(3)" title="Step 3: Partition plots and submit details">
                        <span class="rounded-circle d-flex align-items-center justify-content-center border bg-white text-secondary fw-bold" style="width: 20px; height: 20px; font-size: 0.7rem;" id="step3Badge">3</span>
                        <span id="step3Title">Partitions & Info</span>
                    </div>
                </div>

                <!-- Active Step Guidance Pill -->
                <div class="d-flex align-items-center">
                    <span class="badge bg-white border text-dark fw-normal rounded-pill px-2.5 py-1.5 shadow-2xs d-flex align-items-center gap-1.5" id="stepGuideText" style="font-size: 0.72rem;">
                        <i class="bi bi-geo-alt-fill text-danger"></i>
                        <span><strong>Step 1:</strong> Search location or click anywhere on the map to pinpoint.</span>
                    </span>
                </div>
            </div>

            <div class="map-picker-wrapper position-relative">
                <!-- Top Black Gradient Scrim / Dim Overlay (behind searchbar and controls) -->
                <div class="map-top-gradient-scrim" id="mapTopGradientScrim"></div>

                <!-- Floating Location Search Bar (Reduced Compact UI) -->
                <div class="map-picker-search-box"
                    style="top: 10px; left: 10px; width: 220px; max-width: calc(100% - 230px);">
                    <div class="map-picker-input-group"
                        style="padding: 1px 4px 1px 10px; height: 32px; border-radius: 50rem; box-shadow: 0 4px 14px rgba(0,0,0,0.12);">
                        <i class="bi bi-search text-muted" style="font-size:0.75rem;"></i>
                        <input type="text" id="mapSearchInput" placeholder="Search location..." autocomplete="off"
                            style="font-size: 0.76rem; padding: 2px 6px;">
                        <button type="button" class="map-picker-btn-icon d-none" id="mapSearchClearBtn"
                            title="Clear search" onclick="clearMapSearch()" style="padding: 2px 4px;">
                            <i class="bi bi-x-circle-fill" style="font-size:0.75rem;"></i>
                        </button>
                        <button type="button" class="map-picker-btn-icon text-success" id="mapSearchGpsBtn"
                            title="Use my current GPS location" onclick="useCurrentLocation()"
                            style="padding: 2px 4px;">
                            <i class="bi bi-crosshair" style="font-size:0.85rem;"></i>
                        </button>
                    </div>
                    <!-- Autocomplete Dropdown List -->
                    <div class="map-picker-results" id="mapSearchResults" style="font-size: 0.78rem;"></div>
                </div>

                <!-- Floating Map Action Buttons Overlay (Drawing Tools, Area Badge, 2D/3D Mode) -->
                <div class="map-floating-toolbar d-flex align-items-center gap-1.5 flex-wrap"
                    style="position: absolute; top: 10px; right: 10px; z-index: 1000; justify-content: flex-end;">
                    <!-- Plot Drawing Tools (Revealed after location pinpointed) -->
                    <div class="btn-group btn-group-sm p-0.5 bg-white bg-opacity-95 border rounded-pill shadow-sm" role="group"
                        id="plotDrawToolbar" style="backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); box-shadow: 0 4px 14px rgba(0,0,0,0.12);">
                        <button type="button" class="btn btn-xs rounded-pill px-3 py-1 fw-semibold text-dark active"
                            id="drawModePanBtn" onclick="setPlotDrawMode('pan')" title="Pin Point Location">
                            <i class="bi bi-geo-alt-fill me-1 text-danger"></i>Pin Point
                        </button>
                        <button type="button" class="btn btn-xs rounded-pill px-3 py-1 fw-semibold text-secondary d-none"
                            id="drawModePolygonBtn" onclick="setPlotDrawMode('polygon')"
                            title="Click on the map to draw custom plot boundary corners">
                            <i class="bi bi-pentagon-fill me-1 text-success"></i>Draw Plot
                        </button>
                        <button type="button" class="btn btn-xs rounded-pill px-3 py-1 fw-semibold text-secondary d-none"
                            id="drawModeBoxBtn" onclick="setPlotDrawMode('box')"
                            title="Click two points to draw a box/rectangle plot">
                            <i class="bi bi-bounding-box-circles me-1 text-success"></i>Draw Box
                        </button>
                    </div>

                    <button type="button" class="btn btn-xs btn-outline-danger bg-white bg-opacity-95 rounded-pill px-2.5 py-1 text-xs d-none shadow-sm"
                        id="btnClearDrawnPlot" onclick="clearDrawnPlot()" title="Clear drawn plot boundary"
                        style="backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); box-shadow: 0 4px 14px rgba(0,0,0,0.12);">
                        <i class="bi bi-trash3 me-1"></i>Clear Boundary
                    </button>

                    <button type="button" class="btn btn-xs btn-outline-success bg-white bg-opacity-95 rounded-pill px-2.5 py-1 text-xs d-none fw-semibold shadow-sm"
                        id="btnEditPlotArea" onclick="openLandAreaModal()" title="Click to change target land area in square meters"
                        style="backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); box-shadow: 0 4px 14px rgba(0,0,0,0.12);">
                        <i class="bi bi-rulers me-1"></i><span id="targetAreaBadge">250 m²</span>
                    </button>

                    <!-- 2D / 3D Switcher -->
                    <div class="btn-group btn-group-sm p-0.5 bg-white bg-opacity-95 border rounded-pill shadow-sm" role="group"
                        style="backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); box-shadow: 0 4px 14px rgba(0,0,0,0.12);">
                        <button type="button" class="btn btn-xs rounded-pill px-2.5 py-1 fw-bold text-success active"
                            id="viewMode2DBtn" onclick="switchPickerMode('2d')">
                            <i class="bi bi-map me-1"></i>2D
                        </button>
                        <button type="button" class="btn btn-xs rounded-pill px-2.5 py-1 text-muted" id="viewMode3DBtn"
                            onclick="switchPickerMode('3d')">
                            <i class="bi bi-box-fill me-1 text-primary"></i>3D
                        </button>
                    </div>
                </div>

                <!-- Floating Drawing Status & Instruction Helper -->
                <div class="map-drawing-helper-pill d-none" id="mapDrawingHelper"
                    style="position: absolute; top: 48px; left: 10px; z-index: 1000; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); border: 1px solid rgba(0,0,0,0.14); box-shadow: 0 6px 18px rgba(0,0,0,0.15); border-radius: 50rem; padding: 4px 12px;">
                    <div class="d-flex align-items-center gap-2">
                        <span id="mapDrawingHelperText" class="text-xs fw-semibold text-dark">Click on the map to add
                            boundary corners</span>
                        <button type="button"
                            class="btn btn-xs btn-success rounded-pill px-2 py-0.5 text-xs fw-bold d-none"
                            id="btnFinishPolygon" onclick="finishCurrentPolygon()">
                            <i class="bi bi-check-circle me-1"></i>Finish
                        </button>
                        <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2 py-0.5 text-xs"
                            onclick="cancelPlotDrawing()">
                            Cancel
                        </button>
                    </div>
                </div>

                <!-- Floating Partition Plots Control Overlay (Pops up after plot boundary is drawn) -->
                <div class="position-absolute shadow-sm d-none" id="plotControlOverlay"
                    style="top: 50px; right: 10px; z-index: 1000; width: auto; min-width: 190px; max-width: 220px;">
                    <div class="card border rounded-3 bg-white bg-opacity-95 p-2 shadow-sm"
                        style="backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border-color: rgba(22, 163, 74, 0.25) !important;">
                        <div class="d-flex align-items-center justify-content-between gap-1 mb-1">
                            <span class="fw-bold text-dark d-flex align-items-center gap-1" style="font-size: 0.72rem;">
                                <i class="bi bi-grid-3x3-gap-fill text-success"></i>
                                <span>Plots</span>
                            </span>
                            <span class="badge bg-success-subtle text-success rounded-pill px-1.5 py-0.5"
                                id="plotCalcBadge" style="font-size: 0.65rem;">
                                4 plots (~62.5 m²)
                            </span>
                        </div>
                        <input type="hidden" id="plot_count" name="plot_count" value="4">
                        <input type="hidden" id="singlePlotArea" value="62.5">
                        <!-- Compact Plot Buttons instead of Dropdown -->
                        <div class="d-flex align-items-center justify-content-between gap-1 mb-1">
                            <div class="btn-group btn-group-sm w-100 p-0.5 bg-light rounded-2 border" role="group"
                                aria-label="Partition plots count">
                                <button type="button"
                                    class="btn btn-xs plot-count-btn py-0.5 px-1 rounded-1 fw-bold btn-light text-secondary border-0"
                                    data-count="1" onclick="selectPlotCount(1, this)"
                                    style="font-size: 0.72rem;">1</button>
                                <button type="button"
                                    class="btn btn-xs plot-count-btn py-0.5 px-1 rounded-1 fw-bold btn-light text-secondary border-0"
                                    data-count="2" onclick="selectPlotCount(2, this)"
                                    style="font-size: 0.72rem;">2</button>
                                <button type="button"
                                    class="btn btn-xs plot-count-btn py-0.5 px-1 rounded-1 fw-bold btn-success active text-white border-0"
                                    data-count="4" onclick="selectPlotCount(4, this)"
                                    style="font-size: 0.72rem;">4</button>
                                <button type="button"
                                    class="btn btn-xs plot-count-btn py-0.5 px-1 rounded-1 fw-bold btn-light text-secondary border-0"
                                    data-count="6" onclick="selectPlotCount(6, this)"
                                    style="font-size: 0.72rem;">6</button>
                                <button type="button"
                                    class="btn btn-xs plot-count-btn py-0.5 px-1 rounded-1 fw-bold btn-light text-secondary border-0"
                                    data-count="8" onclick="selectPlotCount(8, this)"
                                    style="font-size: 0.72rem;">8</button>
                            </div>
                        </div>
                        <div class="pt-1 border-top d-flex align-items-center justify-content-between"
                            style="font-size: 0.66rem;">
                            <span class="text-muted" id="drawnBoundarySummary">Standard Grid</span>
                            <button type="button" class="btn btn-link text-success p-0 text-decoration-none fw-semibold"
                                style="font-size: 0.68rem;" onclick="setPlotDrawMode('polygon')">
                                <i class="bi bi-pencil me-0.5"></i>Custom
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 2D Leaflet Map Canvas (Above the Form - Expanded Size) -->
                <div id="pickerMap" style="height: 400px; min-height: 500px; background-color: #e9f2ff;"></div>

                <!-- Floating GPS Go To My Location Button -->
                <div class="position-absolute d-flex flex-column gap-2"
                    style="bottom: 46px; right: 14px; z-index: 1001;">
                    <button type="button" class="map-fab-btn" onclick="useCurrentLocation()" title="Go to My Location"
                        id="btnGoToMyLocation">
                        <i class="bi bi-crosshair text-success fs-5"></i>
                    </button>
                </div>

                <!-- 3D City & Garden Visualization Container -->
                <div id="register3DMapContainer" class="map3d-container" style="height: 40f0px; min-height: 500px;">
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
            <form action="register.php" method="POST" enctype="multipart/form-data" id="landRegistrationForm">
                <input type="hidden" name="action" value="register">
                <input type="hidden" name="lot_polygon" id="lot_polygon">
                <input type="hidden" name="plot_count" id="form_plot_count" value="4">

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



                    <!-- OCT Document Upload (col-12) -->
                    <div class="col-12">
                        <label class="form-label text-secondary fw-semibold text-xs mb-1">UPLOAD OCT DOCUMENT (ORIGINAL
                            CERTIFICATE OF TITLE)</label>
                        <div class="border rounded-4 p-4 text-center bg-light"
                            style="border-style: dashed !important; border-color: var(--drive-border) !important;">
                            <i class="bi bi-file-earmark-pdf fs-1 text-primary mb-2 d-inline-block"></i>
                            <p class="mb-1 text-dark fw-medium" style="font-size: 0.85rem;">Upload Original Certificate
                                of Title (OCT)</p>
                            <span class="text-secondary d-block mb-3" style="font-size: 0.75rem;">Accepted formats: PDF,
                                JPEG, or PNG (Max 10MB)</span>
                            <input type="file" id="oct_document" name="oct_document" accept=".pdf,.jpg,.jpeg,.png"
                                class="d-none" onchange="updateUploadLabel(this)">
                            <button type="button" class="btn btn-sm btn-drive-secondary px-3"
                                onclick="document.getElementById('oct_document').click()">
                                <i class="bi bi-upload me-1"></i>Select OCT File
                            </button>
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

<!-- Modal to Ask for Square Meters Before Drawing -->
<div class="modal fade" id="landAreaModal" tabindex="-1" aria-labelledby="landAreaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content border-0 rounded-4 shadow-xl overflow-hidden">
            <div class="modal-header border-0 bg-success text-white py-3 px-4">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="rounded-circle bg-white text-success d-flex align-items-center justify-content-center flex-shrink-0" style="width: 34px; height: 34px;">
                        <i class="bi bi-rulers fs-6"></i>
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold text-white mb-0" id="landAreaModalLabel">Set Land Area (m²)</h6>
                        <span class="text-white-50" style="font-size: 0.72rem;">Calibrate plot boundary size</span>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-white">
                <p class="text-secondary text-xs mb-3">
                    Enter the total area of your land in square meters. Your plot drawing on the map will automatically be constrained to this exact size.
                </p>

                <div class="mb-3">
                    <label for="modalAreaInput" class="form-label text-dark fw-bold text-xs mb-1">TOTAL SQUARE METERS (m²)</label>
                    <div class="input-group">
                        <input type="number" step="1" min="10" max="100000" class="form-control drive-form-control form-control-lg fw-bold text-success fs-4" id="modalAreaInput" value="250" placeholder="e.g. 250" required>
                        <span class="input-group-text bg-light border text-muted fw-bold">m²</span>
                    </div>
                </div>

                <!-- Quick Presets -->
                <div class="mb-3">
                    <span class="text-muted text-xs d-block mb-1.5 fw-semibold">Quick Presets:</span>
                    <div class="d-flex flex-wrap gap-1.5" id="presetAreaGroup">
                        <button type="button" class="btn btn-xs preset-area-btn btn-outline-secondary rounded-pill px-2.5 py-1" data-val="100" onclick="setPresetArea(100)">100 m²</button>
                        <button type="button" class="btn btn-xs preset-area-btn btn-outline-secondary rounded-pill px-2.5 py-1" data-val="150" onclick="setPresetArea(150)">150 m²</button>
                        <button type="button" class="btn btn-xs preset-area-btn btn-outline-secondary rounded-pill px-2.5 py-1" data-val="200" onclick="setPresetArea(200)">200 m²</button>
                        <button type="button" class="btn btn-xs preset-area-btn btn-success text-white active rounded-pill px-2.5 py-1" data-val="250" onclick="setPresetArea(250)">250 m²</button>
                        <button type="button" class="btn btn-xs preset-area-btn btn-outline-secondary rounded-pill px-2.5 py-1" data-val="500" onclick="setPresetArea(500)">500 m²</button>
                        <button type="button" class="btn btn-xs preset-area-btn btn-outline-secondary rounded-pill px-2.5 py-1" data-val="1000" onclick="setPresetArea(1000)">1,000 m²</button>
                    </div>
                </div>

                <div class="d-grid gap-2 pt-2 border-top">
                    <button type="button" class="btn btn-success rounded-pill py-2 fw-bold d-flex align-items-center justify-content-center gap-2" onclick="confirmLandAreaAndDraw()">
                        <i class="bi bi-pencil-square"></i>
                        <span>Confirm & Draw Boundary</span>
                    </button>
                    <button type="button" class="btn btn-outline-success rounded-pill py-2 fw-semibold text-xs d-flex align-items-center justify-content-center gap-1.5" onclick="autoPlaceExactBoundary()">
                        <i class="bi bi-bounding-box"></i>
                        <span>Auto-Place Exact Plot on Pin</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>



<script>
    const selectedCrops = new Map();

    function updateUploadLabel(input) {
        const file = input.files && input.files[0];
        const label = document.getElementById('file-count');
        if (file) {
            const sizeKB = (file.size / 1024).toFixed(1);
            label.innerHTML = `<i class="bi bi-check-circle-fill me-1"></i>Selected: <strong>${file.name}</strong> (${sizeKB} KB)`;
        } else {
            label.textContent = '';
        }
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

        if (typeof renderSubdivisions === 'function') {
            renderSubdivisions();
        }
    }

    // Leaflet map with geocoding search, coordinate typing & 3D digital twin preview
    document.addEventListener("DOMContentLoaded", function () {
        const defaultLat = 14.5995;
        const defaultLng = 120.9842;

        const initialLatInput = document.getElementById('latitude').value.trim();
        const initialLngInput = document.getElementById('longitude').value.trim();
        const hasInitialCoords = initialLatInput && initialLngInput && !isNaN(parseFloat(initialLatInput)) && !isNaN(parseFloat(initialLngInput));

        const startLat = hasInitialCoords ? parseFloat(initialLatInput) : defaultLat;
        const startLng = hasInitialCoords ? parseFloat(initialLngInput) : defaultLng;

        const map = L.map('pickerMap', {
            zoomControl: false,
            maxZoom: 24
        }).setView([startLat, startLng], hasInitialCoords ? 16 : 13);

        if (hasInitialCoords) {
            setLocation(startLat, startLng, null, 16);
            reverseGeocode(startLat, startLng);
        } else {
            const pinnedText = document.getElementById('pinnedAddressText');
            if (pinnedText) pinnedText.textContent = 'Click on the map or search to pinpoint your land location';

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        const lat = pos.coords.latitude;
                        const lng = pos.coords.longitude;
                        map.setView([lat, lng], 17);
                    },
                    (err) => {
                        console.warn('Geolocation failed or denied, centering default:', err);
                        map.setView([defaultLat, defaultLng], 14);
                    },
                    { enableHighAccuracy: true, timeout: 8000 }
                );
            } else {
                map.setView([defaultLat, defaultLng], 14);
            }
        }

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

        // ─── Target Land Area State & Modal ───
        let targetSquareMeters = parseFloat(document.getElementById('area')?.value) || 250;
        let landAreaModalInstance = null;

        function getLandAreaModal() {
            if (!landAreaModalInstance) {
                const el = document.getElementById('landAreaModal');
                if (el && typeof bootstrap !== 'undefined') {
                    landAreaModalInstance = bootstrap.Modal.getOrCreateInstance(el);
                }
            }
            return landAreaModalInstance;
        }

        window.openLandAreaModal = function (preferredMode = null) {
            const input = document.getElementById('modalAreaInput');
            if (input) {
                input.value = targetSquareMeters || 250;
            }
            updatePresetButtons(targetSquareMeters || 250);

            const modal = getLandAreaModal();
            if (modal) {
                if (preferredMode) {
                    modal._preferredMode = preferredMode;
                }
                modal.show();
            }
        };

        window.setPresetArea = function (val) {
            const input = document.getElementById('modalAreaInput');
            if (input) {
                input.value = val;
            }
            updatePresetButtons(val);
        };

        function updatePresetButtons(val) {
            const buttons = document.querySelectorAll('.preset-area-btn');
            buttons.forEach(btn => {
                const bVal = parseFloat(btn.getAttribute('data-val'));
                if (bVal === parseFloat(val)) {
                    btn.classList.add('active', 'btn-success', 'text-white');
                    btn.classList.remove('btn-outline-secondary');
                } else {
                    btn.classList.remove('active', 'btn-success', 'text-white');
                    btn.classList.add('btn-outline-secondary');
                }
            });
        }

        window.confirmLandAreaAndDraw = function () {
            const input = document.getElementById('modalAreaInput');
            let val = parseFloat(input?.value);
            if (isNaN(val) || val <= 0) {
                alert('Please enter a valid positive land area in square meters (e.g. 250).');
                return;
            }
            targetSquareMeters = Math.round(val * 10) / 10;

            const areaInput = document.getElementById('area');
            if (areaInput) areaInput.value = targetSquareMeters;

            const badge = document.getElementById('targetAreaBadge');
            if (badge) badge.textContent = `${targetSquareMeters.toLocaleString()} m²`;
            const editBtn = document.getElementById('btnEditPlotArea');
            if (editBtn) editBtn.classList.remove('d-none');

            const modal = getLandAreaModal();
            const preferredMode = modal?._preferredMode || 'polygon';
            if (modal) modal.hide();

            // If a polygon already exists, recalibrate its size to match new targetSquareMeters
            if (drawnPolygonLayer && drawnPoints && drawnPoints.length >= 3) {
                const curArea = calculatePolygonArea(drawnPoints);
                if (curArea > 0) {
                    const scaleFactor = Math.sqrt(targetSquareMeters / curArea);
                    const centroid = calculateCentroid(drawnPoints);
                    drawnPoints = drawnPoints.map(p => {
                        return L.latLng(
                            centroid.lat + (p.lat - centroid.lat) * scaleFactor,
                            centroid.lng + (p.lng - centroid.lng) * scaleFactor
                        );
                    });
                    drawnPolygonLayer.setLatLngs(drawnPoints);
                    createVertexMarkers();
                    updateDrawnPolygonStats(false);
                    renderSubdivisions();
                    return;
                }
            }

            // Start drawing
            setPlotDrawMode(preferredMode === 'box' ? 'box' : 'polygon');
            const helper = document.getElementById('mapDrawingHelper');
            const helperText = document.getElementById('mapDrawingHelperText');
            if (helper && helperText) {
                helperText.innerHTML = `<i class="bi bi-rulers text-success me-1"></i><strong>Target: ${targetSquareMeters.toLocaleString()} m²</strong> &mdash; Click on map to place corner 1`;
                helper.classList.remove('d-none');
            }
        };

        window.autoPlaceExactBoundary = function () {
            const input = document.getElementById('modalAreaInput');
            let val = parseFloat(input?.value);
            if (isNaN(val) || val <= 0) {
                alert('Please enter a valid positive land area in square meters (e.g. 250).');
                return;
            }
            targetSquareMeters = Math.round(val * 10) / 10;

            const areaInput = document.getElementById('area');
            if (areaInput) areaInput.value = targetSquareMeters;

            const badge = document.getElementById('targetAreaBadge');
            if (badge) badge.textContent = `${targetSquareMeters.toLocaleString()} m²`;
            const editBtn = document.getElementById('btnEditPlotArea');
            if (editBtn) editBtn.classList.remove('d-none');

            const modal = getLandAreaModal();
            if (modal) modal.hide();

            let center = marker ? marker.getLatLng() : map.getCenter();
            if (!marker) {
                setLocation(center.lat, center.lng);
            }

            const sideMeters = Math.sqrt(targetSquareMeters);
            const halfSide = sideMeters / 2;
            const deltaLat = halfSide / 111320;
            const deltaLng = halfSide / (111320 * Math.cos(center.lat * Math.PI / 180));

            drawnPoints = [
                L.latLng(center.lat + deltaLat, center.lng - deltaLng), // NW
                L.latLng(center.lat + deltaLat, center.lng + deltaLng), // NE
                L.latLng(center.lat - deltaLat, center.lng + deltaLng), // SE
                L.latLng(center.lat - deltaLat, center.lng - deltaLng)  // SW
            ];

            finishCurrentPolygon();
        };

        // ─── Step-by-Step Registration Process Controller ───
        let currentStep = 1;

        function setRegistrationStep(step) {
            currentStep = step;
            const step1Item = document.getElementById('step1Item');
            const step2Item = document.getElementById('step2Item');
            const step3Item = document.getElementById('step3Item');

            const step1Badge = document.getElementById('step1Badge');
            const step2Badge = document.getElementById('step2Badge');
            const step3Badge = document.getElementById('step3Badge');

            const step1Title = document.getElementById('step1Title');
            const step2Title = document.getElementById('step2Title');
            const step3Title = document.getElementById('step3Title');

            const guideText = document.getElementById('stepGuideText');

            const hasPin = (marker !== null);
            const hasPlot = (drawnPolygonLayer !== null && drawnPoints.length >= 3);

            // Step 1 styling
            if (step1Item && step1Badge && step1Title) {
                if (hasPin && step > 1) {
                    step1Item.className = 'd-flex align-items-center gap-1.5 px-2.5 py-1 rounded-pill bg-success-subtle border border-success-subtle transition-all';
                    step1Badge.className = 'rounded-circle d-flex align-items-center justify-content-center text-white bg-success fw-bold';
                    step1Badge.innerHTML = '<i class="bi bi-check-lg" style="font-size:0.68rem;"></i>';
                    step1Title.className = 'fw-bold text-success';
                } else if (step === 1) {
                    step1Item.className = 'd-flex align-items-center gap-1.5 px-2.5 py-1 rounded-pill bg-white border border-success shadow-2xs transition-all';
                    step1Badge.className = 'rounded-circle d-flex align-items-center justify-content-center text-white bg-success fw-bold';
                    step1Badge.innerHTML = '1';
                    step1Title.className = 'fw-bold text-dark';
                } else {
                    step1Item.className = 'd-flex align-items-center gap-1.5 px-2.5 py-1 rounded-pill bg-transparent text-muted transition-all';
                    step1Badge.className = 'rounded-circle d-flex align-items-center justify-content-center border bg-white text-secondary fw-bold';
                    step1Badge.innerHTML = '1';
                    step1Title.className = 'text-muted';
                }
            }

            // Step 2 styling
            if (step2Item && step2Badge && step2Title) {
                if (hasPlot && step > 2) {
                    step2Item.className = 'd-flex align-items-center gap-1.5 px-2.5 py-1 rounded-pill bg-success-subtle border border-success-subtle transition-all';
                    step2Badge.className = 'rounded-circle d-flex align-items-center justify-content-center text-white bg-success fw-bold';
                    step2Badge.innerHTML = '<i class="bi bi-check-lg" style="font-size:0.68rem;"></i>';
                    step2Title.className = 'fw-bold text-success';
                } else if (step === 2) {
                    step2Item.className = 'd-flex align-items-center gap-1.5 px-2.5 py-1 rounded-pill bg-white border border-success shadow-2xs transition-all';
                    step2Badge.className = 'rounded-circle d-flex align-items-center justify-content-center text-white bg-success fw-bold';
                    step2Badge.innerHTML = '2';
                    step2Title.className = 'fw-bold text-dark';
                } else {
                    step2Item.className = 'd-flex align-items-center gap-1.5 px-2.5 py-1 rounded-pill bg-transparent text-muted transition-all';
                    step2Badge.className = 'rounded-circle d-flex align-items-center justify-content-center border bg-white text-secondary fw-bold';
                    step2Badge.innerHTML = '2';
                    step2Title.className = 'text-muted';
                }
            }

            // Step 3 styling
            if (step3Item && step3Badge && step3Title) {
                if (step === 3) {
                    step3Item.className = 'd-flex align-items-center gap-1.5 px-2.5 py-1 rounded-pill bg-white border border-success shadow-2xs transition-all';
                    step3Badge.className = 'rounded-circle d-flex align-items-center justify-content-center text-white bg-success fw-bold';
                    step3Badge.innerHTML = '3';
                    step3Title.className = 'fw-bold text-dark';
                } else {
                    step3Item.className = 'd-flex align-items-center gap-1.5 px-2.5 py-1 rounded-pill bg-transparent text-muted transition-all';
                    step3Badge.className = 'rounded-circle d-flex align-items-center justify-content-center border bg-white text-secondary fw-bold';
                    step3Badge.innerHTML = '3';
                    step3Title.className = 'text-muted';
                }
            }

            // Guide Text update
            if (guideText) {
                if (step === 1) {
                    guideText.innerHTML = '<i class="bi bi-geo-alt-fill text-danger"></i><span><strong>Step 1:</strong> Search location or click anywhere on the map to pinpoint.</span>';
                } else if (step === 2) {
                    guideText.innerHTML = '<i class="bi bi-pentagon-fill text-success"></i><span><strong>Step 2:</strong> Click corners on map to draw plot boundary (min 3 pts). Click "Finish" when done.</span>';
                } else if (step === 3) {
                    guideText.innerHTML = '<i class="bi bi-grid-3x3-gap-fill text-success"></i><span><strong>Step 3:</strong> Boundary created! Choose partition plots & complete land details below.</span>';
                }
            }
        }

        window.goToStep = function (targetStep) {
            if (targetStep === 1) {
                setPlotDrawMode('pan');
                setRegistrationStep(1);
            } else if (targetStep === 2) {
                if (!marker) {
                    alert('Please pinpoint a location on the map first (Step 1).');
                    return;
                }
                if (!drawnPolygonLayer && (!drawnPoints || drawnPoints.length === 0)) {
                    openLandAreaModal('polygon');
                    return;
                }
                setPlotDrawMode('polygon');
                setRegistrationStep(2);
            } else if (targetStep === 3) {
                if (!drawnPolygonLayer || drawnPoints.length < 3) {
                    alert('Please draw and finish your plot boundary first (Step 2).');
                    return;
                }
                setRegistrationStep(3);
                const formEl = document.getElementById('landRegistrationForm');
                if (formEl) formEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        };

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

            renderSubdivisions();
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

            // Reveal Draw Plot and Draw Box buttons now that location is pinpointed
            const polyBtn = document.getElementById('drawModePolygonBtn');
            const boxBtn = document.getElementById('drawModeBoxBtn');
            if (polyBtn) polyBtn.classList.remove('d-none');
            if (boxBtn) boxBtn.classList.remove('d-none');

            // If plot is not drawn yet, automatically zoom and prompt for square meters before drawing!
            if (!drawnPolygonLayer) {
                setRegistrationStep(2);

                // Automatically zoom in close to lot level (zoom 18)
                const targetZoom = 18;
                map.flyTo(latlng, targetZoom, { animate: true, duration: 0.85 });

                // Open modal to ask for square meters before drawing begins
                let prompted = false;
                const promptAreaModal = () => {
                    if (prompted) return;
                    prompted = true;
                    openLandAreaModal('polygon');
                };

                map.once('moveend', promptAreaModal);
                setTimeout(promptAreaModal, 950);
            } else {
                if (zoom) {
                    map.flyTo(latlng, zoom, { animate: true, duration: 0.85 });
                }
                updateLotBoundary(parseFloat(lat), parseFloat(lng));
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

        map.on('dblclick', function (e) {
            if (currentDrawMode === 'polygon' && drawnPoints.length >= 3) {
                L.DomEvent.stopPropagation(e);
                finishCurrentPolygon();
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
                if (!drawnPolygonLayer || drawnPoints.length < 3) {
                    setRegistrationStep(1);
                } else {
                    setRegistrationStep(3);
                }
            } else if (mode === 'polygon') {
                if (polyBtn) {
                    polyBtn.classList.add('active', 'btn-success', 'text-white');
                    polyBtn.classList.remove('text-secondary');
                }
                map.getContainer().style.cursor = 'crosshair';
                if (helper) helper.classList.remove('d-none');
                const targetDisplay = targetSquareMeters ? `${targetSquareMeters.toLocaleString()} m²` : '250 m²';
                if (helperText) helperText.innerHTML = `<i class="bi bi-pentagon-fill text-success me-1"></i><strong>Step 2 (${targetDisplay}):</strong> Click on map to place corner 1`;
                if (finishBtn) finishBtn.classList.add('d-none');
                setRegistrationStep(2);
            } else if (mode === 'box') {
                if (boxBtn) {
                    boxBtn.classList.add('active', 'btn-success', 'text-white');
                    boxBtn.classList.remove('text-secondary');
                }
                map.getContainer().style.cursor = 'crosshair';
                if (helper) helper.classList.remove('d-none');
                const targetDisplay = targetSquareMeters ? `${targetSquareMeters.toLocaleString()} m²` : '250 m²';
                if (helperText) helperText.innerHTML = `<i class="bi bi-bounding-box text-success me-1"></i><strong>Step 2 (${targetDisplay}):</strong> Click on map to anchor corner 1 of the box`;
                if (finishBtn) finishBtn.classList.add('d-none');
                setRegistrationStep(2);
            }
        };

        function cancelTempDrawing() {
            if (drawingTempLine) { map.removeLayer(drawingTempLine); drawingTempLine = null; }
            if (drawingGuideLine) { map.removeLayer(drawingGuideLine); drawingGuideLine = null; }
            if (drawingStartMarker) { map.removeLayer(drawingStartMarker); drawingStartMarker = null; }
            if (boxPreviewLayer) { map.removeLayer(boxPreviewLayer); boxPreviewLayer = null; }
            boxAnchor = null;
        }

        window.cancelPlotDrawing = function () {
            cancelTempDrawing();
            if (!drawnPolygonLayer) {
                drawnPoints = [];
                setRegistrationStep(1);
            } else {
                setRegistrationStep(3);
            }
            setPlotDrawMode('pan');
        };

        function handlePolygonClick(e) {
            drawnPoints.push(e.latlng);
            const count = drawnPoints.length;
            const helperText = document.getElementById('mapDrawingHelperText');
            const finishBtn = document.getElementById('btnFinishPolygon');

            if (count === 1) {
                drawingStartMarker = L.circleMarker(e.latlng, {
                    radius: 9,
                    color: '#ffffff',
                    weight: 3,
                    fillColor: '#198754',
                    fillOpacity: 1
                }).addTo(map);

                drawingStartMarker.on('click', function (ev) {
                    L.DomEvent.stopPropagation(ev);
                    L.DomEvent.preventDefault(ev);
                    if (drawnPoints.length >= 3) {
                        finishCurrentPolygon();
                    } else {
                        alert('Please add at least 3 corner points to close your plot.');
                    }
                });
                drawingStartMarker.bindTooltip('Click here to close plot', { permanent: false, direction: 'top' });

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
                if (helperText) helperText.textContent = `Corner ${count} placed. Click next corner, or click corner 1 / "Finish" to close plot`;
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
                const targetArea = targetSquareMeters || parseFloat(document.getElementById('area')?.value) || 250;
                if (helperText) helperText.innerHTML = `<i class="bi bi-bounding-box text-success me-1"></i>Anchor set! Move mouse to size box (${targetArea.toLocaleString()} m² locked), then click to place`;
            } else {
                const targetArea = targetSquareMeters || parseFloat(document.getElementById('area')?.value) || 250;
                const cosLat = Math.cos(boxAnchor.lat * Math.PI / 180);
                const dxMeters = Math.max(3, Math.abs(e.latlng.lng - boxAnchor.lng) * 111320 * cosLat);
                const dyMeters = targetArea / dxMeters;
                const deltaLat = dyMeters / 111320;
                const latSign = e.latlng.lat >= boxAnchor.lat ? 1 : -1;
                const constrainedLat = boxAnchor.lat + (latSign * deltaLat);

                const minLat = Math.min(boxAnchor.lat, constrainedLat);
                const maxLat = Math.max(boxAnchor.lat, constrainedLat);
                const minLng = Math.min(boxAnchor.lng, e.latlng.lng);
                const maxLng = Math.max(boxAnchor.lng, e.latlng.lng);

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
                const targetArea = targetSquareMeters || parseFloat(document.getElementById('area')?.value) || 250;
                const cosLat = Math.cos(boxAnchor.lat * Math.PI / 180);
                const dxMeters = Math.max(3, Math.abs(e.latlng.lng - boxAnchor.lng) * 111320 * cosLat);
                const dyMeters = targetArea / dxMeters;
                const deltaLat = dyMeters / 111320;
                const latSign = e.latlng.lat >= boxAnchor.lat ? 1 : -1;
                const constrainedLat = boxAnchor.lat + (latSign * deltaLat);
                const constrainedCorner = L.latLng(constrainedLat, e.latlng.lng);

                const bounds = L.latLngBounds(boxAnchor, constrainedCorner);
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

                const helperText = document.getElementById('mapDrawingHelperText');
                if (helperText) {
                    helperText.innerHTML = `<i class="bi bi-rulers text-success me-1"></i><strong>${Math.round(targetArea)} m² Locked</strong> (${Math.round(dxMeters)}m × ${Math.round(dyMeters)}m) &mdash; Click to place box`;
                }
            }
        }

        window.finishCurrentPolygon = function () {
            if (!drawnPoints || drawnPoints.length < 3) {
                alert('Please click at least 3 points on the map to create a closed plot boundary.');
                return;
            }

            // Calibrate/scale to exact targetSquareMeters so drawing matches inputted size!
            const curArea = calculatePolygonArea(drawnPoints);
            const targetArea = targetSquareMeters || parseFloat(document.getElementById('area')?.value) || 250;
            if (curArea > 0 && targetArea > 0) {
                const scaleFactor = Math.sqrt(targetArea / curArea);
                const centroid = calculateCentroid(drawnPoints);
                drawnPoints = drawnPoints.map(p => {
                    return L.latLng(
                        centroid.lat + (p.lat - centroid.lat) * scaleFactor,
                        centroid.lng + (p.lng - centroid.lng) * scaleFactor
                    );
                });
            }

            // Preserve drawn points so they are never wiped
            const finalPoints = drawnPoints.slice();

            // Clear temporary drawing overlays only
            cancelTempDrawing();

            // Restore the points
            drawnPoints = finalPoints;

            if (drawnPolygonLayer) {
                map.removeLayer(drawnPolygonLayer);
                drawnPolygonLayer = null;
            }
            if (boundaryPoly) {
                map.removeLayer(boundaryPoly);
                boundaryPoly = null;
            }

            // Create solid closed polygon layer
            drawnPolygonLayer = L.polygon(drawnPoints, {
                color: '#198754',
                weight: 3,
                fillColor: '#22c55e',
                fillOpacity: 0.25
            }).addTo(map);

            createVertexMarkers();
            updateDrawnPolygonStats(true);
            renderSubdivisions();

            const clearBtn = document.getElementById('btnClearDrawnPlot');
            if (clearBtn) clearBtn.classList.remove('d-none');
            const formClearBtn = document.getElementById('btnFormClearDrawn');
            if (formClearBtn) formClearBtn.classList.remove('d-none');

            // Pop up the Partition Plots Overlay now that plot boundary is drawn
            const plotOverlay = document.getElementById('plotControlOverlay');
            if (plotOverlay) {
                plotOverlay.classList.remove('d-none');
            }

            // Hide the drawing helper
            const helper = document.getElementById('mapDrawingHelper');
            if (helper) helper.classList.add('d-none');

            // Switch to pan mode cleanly without clearing drawn points
            currentDrawMode = 'pan';
            map.getContainer().style.cursor = '';
            const panBtn = document.getElementById('drawModePanBtn');
            const polyBtn = document.getElementById('drawModePolygonBtn');
            const boxBtn = document.getElementById('drawModeBoxBtn');
            [polyBtn, boxBtn].forEach(b => {
                if (b) {
                    b.classList.remove('active', 'btn-success', 'text-white');
                    b.classList.add('text-secondary');
                }
            });
            if (panBtn) {
                panBtn.classList.add('active', 'text-dark');
                panBtn.classList.remove('text-secondary');
            }

            setRegistrationStep(3);
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

            targetSquareMeters = roundedArea;
            const badge = document.getElementById('targetAreaBadge');
            if (badge) badge.textContent = `${roundedArea.toLocaleString()} m²`;
            const editBtn = document.getElementById('btnEditPlotArea');
            if (editBtn) editBtn.classList.remove('d-none');

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

            if (!drawnPolygonLayer || drawnPoints.length < 3) {
                return;
            }

            const bounds = drawnPolygonLayer.getBounds();
            if (!bounds) return;

            const count = parseInt(document.getElementById('plot_count').value) || 4;
            const totalArea = parseFloat(document.getElementById('area').value) || 200;
            const singlePlotArea = totalArea > 0 ? (totalArea / count).toFixed(1) : Math.round(totalArea / count);

            const cols = Math.ceil(Math.sqrt(count));
            const rows = Math.ceil(count / cols);
            const latStep = (bounds.getNorth() - bounds.getSouth()) / rows;
            const lngStep = (bounds.getEast() - bounds.getWest()) / cols;

            const permittedCrops = Array.from(selectedCrops.values()).map(c => c.label);

            let plotIndex = 1;
            for (let r = 0; r < rows; r++) {
                for (let c = 0; c < cols; c++) {
                    if (plotIndex > count) break;

                    const pMaxLat = bounds.getNorth() - (r * latStep);
                    const pMinLat = bounds.getNorth() - ((r + 1) * latStep);
                    const pMinLng = bounds.getWest() + (c * lngStep);
                    const pMaxLng = bounds.getWest() + ((c + 1) * lngStep);

                    const plotSquare = [
                        [pMaxLat, pMinLng],
                        [pMaxLat, pMaxLng],
                        [pMinLat, pMaxLng],
                        [pMinLat, pMinLng]
                    ];

                    const plotPoly = L.polygon(plotSquare, {
                        color: '#16a34a',
                        weight: 1.5,
                        fillColor: '#22c55e',
                        fillOpacity: 0.22,
                        dashArray: '3, 4'
                    }).addTo(map);

                    const plotName = `Plot A-${plotIndex}`;
                    const cropName = permittedCrops.length > 0 ? permittedCrops[(plotIndex - 1) % permittedCrops.length] : 'Available for Gardeners';

                    plotPoly.bindTooltip(
                        `<div style="font-family:'Outfit',sans-serif;font-size:11px;line-height:1.3;padding:1px;">
                            <strong class="text-success"><i class="bi bi-grid-3x3-gap-fill me-1"></i>${plotName}</strong><br>
                            <span class="text-dark font-monospace">${singlePlotArea} m²</span><br>
                            <span class="badge bg-success-subtle text-success mt-1" style="font-size:9px;">${cropName}</span>
                        </div>`,
                        { permanent: false, direction: 'center' }
                    );

                    // Add subtle center badge icon
                    const centerLat = (pMinLat + pMaxLat) / 2;
                    const centerLng = (pMinLng + pMaxLng) / 2;
                    const centerLabel = L.marker([centerLat, centerLng], {
                        icon: L.divIcon({
                            className: 'partition-plot-label',
                            html: `<div style="background:rgba(22,163,74,0.85);color:#fff;font-size:9.5px;font-weight:700;padding:1px 6px;border-radius:10px;box-shadow:0 1px 4px rgba(0,0,0,0.25);pointer-events:none;white-space:nowrap;transform:translate(-50%,-50%);">${plotName}</div>`,
                            iconSize: [0, 0]
                        }),
                        interactive: false
                    }).addTo(map);

                    subdivisionLayers.push(plotPoly);
                    subdivisionLayers.push(centerLabel);

                    plotIndex++;
                }
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

            // Hide the Plot Control Overlay when plot is cleared
            const plotOverlay = document.getElementById('plotControlOverlay');
            if (plotOverlay) {
                plotOverlay.classList.add('d-none');
            }

            setRegistrationStep(2);
            setPlotDrawMode('polygon');
            const helper = document.getElementById('mapDrawingHelper');
            const helperText = document.getElementById('mapDrawingHelperText');
            if (helper && helperText) {
                helperText.innerHTML = '<i class="bi bi-pentagon-fill text-success me-1"></i>Plot cleared. Click corners on map to redraw your boundary.';
                helper.classList.remove('d-none');
            }
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
        window.selectPlotCount = function (count, btn) {
            const hiddenInput = document.getElementById('plot_count');
            if (hiddenInput) hiddenInput.value = count;
            const formPlotCount = document.getElementById('form_plot_count');
            if (formPlotCount) formPlotCount.value = count;

            document.querySelectorAll('.plot-count-btn').forEach(b => {
                b.classList.remove('btn-success', 'active', 'text-white');
                b.classList.add('btn-light', 'text-secondary');
            });
            if (btn) {
                btn.classList.remove('btn-light', 'text-secondary');
                btn.classList.add('btn-success', 'active', 'text-white');
            }
            updatePlotCalculation();
        };

        window.updatePlotCalculation = function (syncTarget = true) {
            const areaInput = document.getElementById('area');
            const totalArea = parseFloat(areaInput ? areaInput.value : 0) || 0;
            if (syncTarget && totalArea > 0) {
                targetSquareMeters = totalArea;
                const badge = document.getElementById('targetAreaBadge');
                if (badge) badge.textContent = `${totalArea.toLocaleString()} m²`;
                const editBtn = document.getElementById('btnEditPlotArea');
                if (editBtn) editBtn.classList.remove('d-none');
            }
            const plotCountSelect = document.getElementById('plot_count');
            const count = parseInt(plotCountSelect ? plotCountSelect.value : 4) || 4;
            const singleAreaInput = document.getElementById('singlePlotArea');
            const badge = document.getElementById('plotCalcBadge');
            const previewGrid = document.getElementById('plotGridPreviewBadges');

            const formPlotCount = document.getElementById('form_plot_count');
            if (formPlotCount) formPlotCount.value = count;

            // Sync button active state with count
            const activeBtn = document.querySelector(`.plot-count-btn[data-count="${count}"]`);
            if (activeBtn) {
                document.querySelectorAll('.plot-count-btn').forEach(b => {
                    b.classList.remove('btn-success', 'active', 'text-white');
                    b.classList.add('btn-light', 'text-secondary');
                });
                activeBtn.classList.remove('btn-light', 'text-secondary');
                activeBtn.classList.add('btn-success', 'active', 'text-white');
            }

            const singleArea = totalArea > 0 ? (totalArea / count).toFixed(1) : '—';
            if (singleAreaInput) singleAreaInput.value = singleArea;
            if (badge) {
                badge.textContent = totalArea > 0
                    ? `${count} plot${count > 1 ? 's' : ''} (~${singleArea} m²)`
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
            const plotOverlay = document.getElementById('plotControlOverlay');
            const scrim = document.getElementById('mapTopGradientScrim');

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
                if (plotOverlay) plotOverlay.classList.add('d-none');
                if (scrim) scrim.classList.add('d-none');
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
                if (plotOverlay && drawnPolygonLayer) plotOverlay.classList.remove('d-none');
                if (scrim) scrim.classList.remove('d-none');
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