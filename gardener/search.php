<?php
$base_path = '../';
$page_title = "Search Lands";
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ensure mock lands exists in session and has crop compatibility tags
if (!isset($_SESSION['mock_lands'])) {
    $_SESSION['mock_lands'] = [
        [
            'id' => 1,
            'title' => 'Sunnyvale Gardening Lot',
            'landowner' => 'John Landowner',
            'address' => '124 Green Ave, Sunnyvale',
            'latitude' => 14.5995,
            'longitude' => 120.9842,
            'area' => 250.00,
            'status' => 'pending',
            'reason' => '',
            'description' => 'A spacious lot with fertile soil and partial shade, perfect for root vegetables like carrots and potatoes.',
            'crops' => ['Root Vegetables', 'Tuber Crops']
        ],
        [
            'id' => 2,
            'title' => 'Downtown Rooftop Garden',
            'landowner' => 'John Landowner',
            'address' => '45 Main St, Business District',
            'latitude' => 14.6010,
            'longitude' => 120.9890,
            'area' => 85.50,
            'status' => 'approved',
            'reason' => '',
            'description' => 'An elevated deck prepared with planters and drip irrigation, ideal for leafy greens and culinary herbs.',
            'crops' => ['Leafy Greens', 'Herbs']
        ],
        [
            'id' => 3,
            'title' => 'Riverdale Acres',
            'landowner' => 'Robert Johnson',
            'address' => 'Riverside Dr, Block B',
            'latitude' => 14.5950,
            'longitude' => 120.9780,
            'area' => 500.00,
            'status' => 'approved',
            'reason' => '',
            'description' => 'Large idle pasture near the riverbed. High soil quality, direct sunlight access. Excellent for fruits, tomatoes and legumes.',
            'crops' => ['Fruits', 'Legumes', 'Leafy Greens']
        ],
        [
            'id' => 4,
            'title' => 'Eastside Clay Meadows',
            'landowner' => 'Sarah Connor',
            'address' => '789 East Blvd, Clay District',
            'latitude' => 14.6120,
            'longitude' => 121.0020,
            'area' => 180.00,
            'status' => 'approved',
            'reason' => '',
            'description' => 'Rich heavy clay loam soil retaining moisture well. Best suited for cabbage, broccoli, and tuber crops.',
            'crops' => ['Cruciferous', 'Tuber Crops']
        ]
    ];
} else {
    // Ensure all existing mock lands have crops tags
    foreach ($_SESSION['mock_lands'] as &$l) {
        if (!isset($l['crops'])) {
            if (stripos($l['title'], 'Sunnyvale') !== false) {
                $l['crops'] = ['Root Vegetables', 'Tuber Crops'];
            } elseif (stripos($l['title'], 'Rooftop') !== false) {
                $l['crops'] = ['Leafy Greens', 'Herbs'];
            } else {
                $l['crops'] = ['Fruits', 'Legumes', 'Leafy Greens'];
            }
        }
    }
}

// Ensure mock plots exists in session
if (!isset($_SESSION['mock_plots'])) {
    $_SESSION['mock_plots'] = [
        ['id' => 1, 'land_id' => 2, 'plot_number' => 'Plot A-1', 'area' => 20.0, 'status' => 'occupied'],
        ['id' => 2, 'land_id' => 2, 'plot_number' => 'Plot A-2', 'area' => 20.0, 'status' => 'occupied'],
        ['id' => 3, 'land_id' => 2, 'plot_number' => 'Plot B-1', 'area' => 22.0, 'status' => 'available'],
        ['id' => 4, 'land_id' => 2, 'plot_number' => 'Plot B-2', 'area' => 23.5, 'status' => 'available'],
        ['id' => 5, 'land_id' => 3, 'plot_number' => 'Plot R-1', 'area' => 100.0, 'status' => 'available'],
        ['id' => 6, 'land_id' => 4, 'plot_number' => 'Plot E-1', 'area' => 90.0, 'status' => 'available'],
        ['id' => 7, 'land_id' => 4, 'plot_number' => 'Plot E-2', 'area' => 90.0, 'status' => 'available']
    ];
}

// Handle request submission (adds new request in PHP session)
$success_msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_request') {
    $land_id = intval($_POST['land_id']);
    $plot_id = intval($_POST['plot_id']);
    $purpose = htmlspecialchars(trim($_POST['purpose']));
    $duration = htmlspecialchars(trim($_POST['duration']));

    $land_title = "";
    foreach ($_SESSION['mock_lands'] as $land) {
        if ($land['id'] === $land_id) {
            $land_title = $land['title'];
            break;
        }
    }

    $plot_num = "Any Plot";
    foreach ($_SESSION['mock_plots'] as $plot) {
        if ($plot['id'] === $plot_id) {
            $plot_num = $plot['plot_number'];
            break;
        }
    }

    if (!isset($_SESSION['mock_requests'])) {
        $_SESSION['mock_requests'] = [];
    }

    $_SESSION['mock_requests'][] = [
        'id' => count($_SESSION['mock_requests']) + 1,
        'gardener' => $_SESSION['user_name'] ?? 'Mary Gardener',
        'email' => 'gardener@garden.com',
        'phone' => '09333456789',
        'land_title' => $land_title,
        'plot_num' => $plot_num,
        'purpose' => $purpose,
        'duration' => $duration,
        'status' => 'pending',
        'notes' => ''
    ];
    $success_msg = "Plot application submitted successfully! Tracking is available under 'Requests'.";
}

$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$crop_options = ['Root Vegetables', 'Tuber Crops', 'Leafy Greens', 'Herbs', 'Fruits', 'Legumes', 'Cruciferous'];
?>

<main class="flex-1 bg-white rounded-[24px] border border-drive-border flex flex-col overflow-hidden h-full">
    <!-- Toolbar/Title Bar -->
    <div class="h-14 border-b border-drive-border px-6 flex items-center justify-between flex-shrink-0 bg-white">
        <div>
            <h1 class="text-sm font-bold text-drive-text-main flex items-center gap-2">
                <i class="bi bi-search text-drive-primary"></i> Search Lands
            </h1>
        </div>
        <div class="flex items-center gap-2">

            <button
                class="md:hidden bg-drive-canvas hover:bg-drive-surface-hover border border-drive-border px-3 py-1.5 rounded-lg text-xs font-semibold text-drive-text-main flex items-center gap-1"
                data-bs-toggle="collapse" data-bs-target="#mobileFilterCollapse">
                <i class="bi bi-sliders"></i> Filters
            </button>
        </div>
    </div>

    <!-- main workspace container split into filters and full-height map -->
    <div class="flex flex-1 overflow-hidden relative">

        <!-- Left Filter Panel -->
        <aside class="w-72 border-r border-drive-border bg-white p-6 overflow-y-auto flex-shrink-0 hidden md:block">
            <div class="flex flex-col gap-6 m-0">
                <!-- Search text box -->
                <div>
                    <h3 class="text-xs font-bold text-drive-text-sub uppercase tracking-wider mb-2">SEARCH</h3>
                    <div class="relative flex items-center">
                        <i class="bi bi-search absolute left-3.5 text-drive-text-sub text-xs"></i>
                        <input type="text" id="searchQueryInput" placeholder="Name, address, crops..."
                            value="<?php echo htmlspecialchars($search_query); ?>"
                            class="w-full bg-white border border-drive-border rounded-xl pl-9 pr-4 py-2 text-xs focus:outline-none focus:border-drive-primary transition-colors">
                    </div>
                </div>

                <!-- Filter section: Plant Suitability -->
                <div>
                    <h3 class="text-xs font-bold text-drive-text-sub uppercase tracking-wider mb-3">SUITABILITY
                    </h3>
                    <div class="flex flex-col gap-2">
                        <?php foreach ($crop_options as $crop): ?>
                            <label
                                class="flex items-center gap-2.5 text-xs text-drive-text-main font-medium cursor-pointer">
                                <input type="checkbox" value="<?php echo $crop; ?>"
                                    class="crop-checkbox w-4 h-4 rounded border-drive-border text-drive-primary focus:ring-0 cursor-pointer">
                                <span><?php echo $crop; ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Filter section: Minimum Land Area -->
                <div>
                    <h3 class="text-xs font-bold text-drive-text-sub uppercase tracking-wider mb-2">MIN SIZE (m²)
                    </h3>
                    <div class="flex items-center gap-3">
                        <input type="range" id="minAreaInput" min="0" max="500" step="50" value="0"
                            class="w-full h-1 bg-[#e1e3e1] rounded-lg appearance-none cursor-pointer accent-drive-primary">
                        <span id="areaVal" class="text-xs font-bold text-drive-text-sub min-w-[50px]">0 m²</span>
                    </div>
                </div>

                <!-- Filter section: Availability -->
                <div>
                    <h3 class="text-xs font-bold text-drive-text-sub uppercase tracking-wider mb-3">AVAILABILITY</h3>
                    <label class="flex items-center gap-2.5 text-xs text-drive-text-main font-medium cursor-pointer">
                        <input type="checkbox" id="availableOnlyInput"
                            class="w-4 h-4 rounded border-drive-border text-drive-primary focus:ring-0 cursor-pointer">
                        <span>Has Available Plots</span>
                    </label>
                </div>

                <hr class="border-drive-border my-1">

                <div class="flex gap-2">
                    <button id="clearFiltersBtn"
                        class="flex-1 border border-drive-border text-drive-text-sub hover:bg-drive-surface-hover font-semibold py-2 rounded-xl text-xs transition-colors">
                        Clear
                    </button>
                </div>
            </div>
        </aside>

        <!-- Right full screen interactive Leaflet Map -->
        <div class="flex-grow-1 h-full w-full relative" style="z-index: 1;">
            <div id="alertPlaceholder" class="absolute top-4 left-4 right-4 z-50 pointer-events-auto"></div>

            <!-- Collapsible mobile filters overlay -->
            <div class="collapse md:hidden absolute top-4 left-4 right-4 z-50 bg-white border border-drive-border rounded-2xl shadow-xl p-4 max-h-[85vh] overflow-y-auto"
                id="mobileFilterCollapse">
                <div class="flex flex-col gap-4 m-0">
                    <div class="flex justify-between items-center pb-2 border-b border-drive-border">
                        <h4 class="text-xs font-bold text-drive-text-main m-0">Filters</h4>
                        <button type="button" class="btn-close" data-bs-toggle="collapse"
                            data-bs-target="#mobileFilterCollapse"></button>
                    </div>

                    <div>
                        <h4 class="text-xs font-bold text-drive-text-sub uppercase tracking-wider mb-2">SEARCH
                        </h4>
                        <input type="text" id="mobileSearchQueryInput" placeholder="Name, address..."
                            class="w-full bg-white border border-drive-border rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-drive-primary">
                    </div>

                    <div>
                        <h4 class="text-xs font-bold text-drive-text-sub uppercase tracking-wider mb-2">SUITABILITY
                        </h4>
                        <div class="grid grid-cols-2 gap-2">
                            <?php foreach ($crop_options as $crop): ?>
                                <label class="flex items-center gap-2 text-xs text-drive-text-main cursor-pointer">
                                    <input type="checkbox" value="<?php echo $crop; ?>"
                                        class="mobile-crop-checkbox w-4 h-4 rounded border-drive-border text-drive-primary focus:ring-0">
                                    <span><?php echo $crop; ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-xs font-bold text-drive-text-sub uppercase tracking-wider mb-2">AVAILABILITY
                        </h4>
                        <label class="flex items-center gap-2 text-xs text-drive-text-main cursor-pointer">
                            <input type="checkbox" id="mobileAvailableOnlyInput"
                                class="w-4 h-4 rounded border-drive-border text-drive-primary focus:ring-0">
                            <span>Has Available Plots</span>
                        </label>
                    </div>

                    <div class="flex gap-2 pt-2">
                        <button id="mobileClearFiltersBtn"
                            class="flex-1 bg-white border border-drive-border text-drive-text-sub font-semibold py-2 rounded-xl text-xs">
                            Clear
                        </button>
                        <button type="button"
                            class="flex-1 bg-drive-primary text-white font-semibold py-2 rounded-xl text-xs"
                            data-bs-toggle="collapse" data-bs-target="#mobileFilterCollapse">
                            Close
                        </button>
                    </div>
                </div>
            </div>

            <!-- Leaflet Container -->
            <div id="searchMap" style="width: 100%; height: 100%; min-height: 500px; background-color: #f0f4f9;"></div>
        </div>
    </div>
</main>

<!-- Lease Request Modal Form (Single Dynamic Modal) -->
<div class="modal fade" id="requestPlotModal" tabindex="-1" aria-hidden="true" style="z-index: 9999;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content drive-modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold text-dark">Apply for Plot</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-start">
                <form id="requestPlotForm">
                    <input type="hidden" name="land_id" id="modal_land_id" value="">
                    <input type="hidden" name="plot_id" id="modal_plot_id" value="">

                    <div
                        class="p-3 bg-drive-canvas border border-drive-border rounded-xl mb-3 text-xs text-drive-text-sub">
                        <strong>Land:</strong> <span id="modal_land_title"
                            class="font-semibold text-drive-text-main"></span><br>
                        <strong>Selected Plot:</strong> <span id="modal_plot_number"
                            class="font-semibold text-drive-text-main"></span> (<span id="modal_plot_area"></span> m²)
                    </div>

                    <div class="mb-3">
                        <label for="duration"
                            class="block text-[10px] font-bold text-drive-text-sub uppercase tracking-wider mb-2">DURATION</label>
                        <select
                            class="w-full bg-white border border-drive-border rounded-xl px-4 py-2 text-sm focus:outline-none focus:border-drive-primary"
                            id="duration" name="duration" required>
                            <option value="3 Months">3 Months</option>
                            <option value="6 Months">6 Months</option>
                            <option value="1 Year">1 Year</option>
                            <option value="Indefinite">Indefinite</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="purpose"
                            class="block text-[10px] font-bold text-drive-text-sub uppercase tracking-wider mb-2">PURPOSE</label>
                        <textarea
                            class="w-full bg-white border border-drive-border rounded-xl px-4 py-2 text-sm focus:outline-none focus:border-drive-primary"
                            id="purpose" name="purpose" rows="3" required
                            placeholder="Detail the crops you plan to cultivate..."></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-drive-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-drive-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Seed data passed from PHP
    window.allLands = <?php echo json_encode(array_filter($_SESSION['mock_lands'], function ($l) {
        return $l['status'] === 'approved'; })); ?>;
    window.allPlots = <?php echo json_encode($_SESSION['mock_plots']); ?>;
</script>
<script src="<?php echo $base_path; ?>assets/js/search.js"></script>

<?php include '../includes/footer.php'; ?>