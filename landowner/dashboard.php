<?php
$base_path = '../';
$page_title = "Gardens Map";
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';

if (session_status() == PHP_SESSION_NONE)
    session_start();
$_SESSION['active_role'] = 'landowner';
if (!isset($_SESSION['user_name']))
    $_SESSION['user_name'] = 'John Landowner';

if (!isset($_SESSION['mock_lands'])) {
    $_SESSION['mock_lands'] = [
        ['id' => 1, 'title' => 'Sunnyvale Gardening Lot', 'landowner' => 'John Landowner', 'address' => '124 Green Ave, Sunnyvale', 'latitude' => 14.5995, 'longitude' => 120.9842, 'area' => 250.00, 'status' => 'pending', 'reason' => '', 'description' => 'A spacious lot with fertile soil and partial shade, perfect for root vegetables like carrots and potatoes.', 'crops' => ['Root Vegetables', 'Tuber Crops']],
        ['id' => 2, 'title' => 'Downtown Rooftop Garden', 'landowner' => 'John Landowner', 'address' => '45 Main St, Business District', 'latitude' => 14.6010, 'longitude' => 120.9890, 'area' => 85.50, 'status' => 'approved', 'reason' => '', 'description' => 'An elevated deck prepared with planters and drip irrigation, ideal for leafy greens and culinary herbs.', 'crops' => ['Leafy Greens', 'Herbs']],
        ['id' => 3, 'title' => 'Riverdale Acres', 'landowner' => 'Robert Johnson', 'address' => 'Riverside Dr, Block B', 'latitude' => 14.5950, 'longitude' => 120.9780, 'area' => 500.00, 'status' => 'approved', 'reason' => '', 'description' => 'Large idle pasture near the riverbed. High soil quality, direct sunlight access. Excellent for fruits, tomatoes and legumes.', 'crops' => ['Fruits', 'Legumes', 'Leafy Greens']],
        ['id' => 4, 'title' => 'Eastside Clay Meadows', 'landowner' => 'Sarah Connor', 'address' => '789 East Blvd, Clay District', 'latitude' => 14.6120, 'longitude' => 121.0020, 'area' => 180.00, 'status' => 'approved', 'reason' => '', 'description' => 'Rich heavy clay loam soil retaining moisture well. Best suited for cabbage, broccoli, and tuber crops.', 'crops' => ['Cruciferous', 'Tuber Crops']],
    ];
}
if (!isset($_SESSION['mock_plots'])) {
    $_SESSION['mock_plots'] = [
        ['id' => 1, 'land_id' => 2, 'plot_number' => 'Plot A-1', 'area' => 20.0, 'status' => 'occupied'],
        ['id' => 2, 'land_id' => 2, 'plot_number' => 'Plot A-2', 'area' => 20.0, 'status' => 'occupied'],
        ['id' => 3, 'land_id' => 2, 'plot_number' => 'Plot B-1', 'area' => 22.0, 'status' => 'available'],
        ['id' => 4, 'land_id' => 2, 'plot_number' => 'Plot B-2', 'area' => 23.5, 'status' => 'available'],
        ['id' => 5, 'land_id' => 3, 'plot_number' => 'Plot R-1', 'area' => 100.0, 'status' => 'available'],
        ['id' => 6, 'land_id' => 4, 'plot_number' => 'Plot E-1', 'area' => 90.0, 'status' => 'available'],
        ['id' => 7, 'land_id' => 4, 'plot_number' => 'Plot E-2', 'area' => 90.0, 'status' => 'available'],
    ];
}

$lands_data = $_SESSION['mock_lands'];
$plots_data = $_SESSION['mock_plots'];
foreach ($lands_data as &$land) {
    $lp = array_filter($plots_data, fn($p) => $p['land_id'] == $land['id']);
    $land['total_plots'] = count($lp);
    $land['occupied_plots'] = count(array_filter($lp, fn($p) => $p['status'] === 'occupied'));
}
unset($land);

$approved_count = count(array_filter($lands_data, fn($l) => $l['status'] === 'approved'));
$pending_count = count(array_filter($lands_data, fn($l) => $l['status'] === 'pending'));
?>

<style>
    /* Filter chips positioned at top of map on desktop */
    .mobile-map-chips-bar {
        top: 14px !important;
    }

    .leaflet-top.leaflet-right {
        top: 60px !important;
    }

    /* Mobile edge-to-edge view */
    @media (max-width: 768px) {
        header {
            display: none !important;
        }

        body {
            overflow: hidden !important;
        }

        .flex.flex-1 {
            padding: 0 !important;
            height: 100vh !important;
        }

        .workspace-surface.landowner-map-page {
            height: 100vh !important;
            border-radius: 0 !important;
            border: none !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .mobile-map-chips-bar {
            top: 64px !important;
        }

        .leaflet-top.leaflet-right {
            top: 110px !important;
        }
    }
</style>

<main class="workspace-surface landowner-map-page d-flex flex-column overflow-hidden h-100">

    <!-- Desktop Toolbar -->
    <div class="toolbar border-bottom d-none d-md-flex align-items-center justify-content-between px-4 py-2.5 bg-white flex-shrink-0">
        <h1 class="fs-5 fw-semibold m-0 text-dark d-flex align-items-center gap-2">
            <i data-lucide="map-pin" class="text-success" style="width:20px;height:20px;"></i>
            Gardens Map
        </h1>
        <a href="register.php" class="btn btn-drive-primary btn-sm px-4 d-flex align-items-center gap-2 rounded-pill">
            <i data-lucide="plus" style="width:15px;height:15px;"></i> Register Land
        </a>
    </div>

    <!-- Map Container -->
    <div class="flex-grow-1 position-relative overflow-hidden w-100 h-100" style="min-height:0;">
        <div class="landowner-map-wrapper">

            <!-- Floating Brand Overlay (Mobile Only) -->
            <div class="floating-map-header d-md-none">
                <div class="d-flex align-items-center gap-2">
                    <img src="<?php echo $base_path; ?>logo.jpeg" alt="IdleLand Logo" class="rounded-circle shadow-sm" style="width:32px;height:32px;object-fit:cover;">
                    <div>
                        <div class="fw-bold text-dark font-['Outfit'] d-flex align-items-center gap-1.5" style="font-size:0.92rem;line-height:1;">
                            <span class="text-success">Idle</span>Land
                            <span class="badge bg-success-subtle text-success rounded-pill px-2 py-0.5" style="font-size:0.68rem;font-weight:600;">Gardens Map</span>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <a href="register.php" class="btn btn-drive-primary btn-sm rounded-pill px-3 py-1.5 text-xs d-flex align-items-center gap-1 shadow-sm">
                        <i data-lucide="plus" style="width:14px;height:14px;"></i>
                        <span>Register</span>
                    </a>
                </div>
            </div>

            <!-- Filter chips -->
            <div class="mobile-map-chips-bar" id="mapChipsBar">
                <button type="button" class="map-chip active" onclick="filterMapLands('all',this)">
                    All (<?php echo count($lands_data); ?>)
                </button>
                <button type="button" class="map-chip" onclick="filterMapLands('my',this)">
                    <i class="bi bi-person-fill me-1"></i>Mine
                </button>
                <button type="button" class="map-chip" onclick="filterMapLands('approved',this)">
                    <i class="bi bi-check-circle-fill me-1" style="color:#198754;"></i>Approved
                </button>
                <button type="button" class="map-chip" onclick="filterMapLands('pending',this)">
                    <i class="bi bi-clock-fill me-1" style="color:#ffc107;"></i>Pending
                </button>
            </div>

            <!-- Leaflet Map -->
            <div id="landownerMainMap" style="width:100%;height:100%;min-height:450px;z-index:1;"></div>

            <!-- Desktop FAB stack (right side) -->
            <div class="position-absolute d-flex flex-column gap-2" style="bottom:28px;right:16px;z-index:1001;">
                <button type="button" class="map-fab-btn" onclick="recenterLandownerMap()" title="Fit all gardens">
                    <i data-lucide="locate" style="width:19px;height:19px;" class="text-success"></i>
                </button>
            </div>

            <!-- Floating Register FAB — mobile only -->
            <a href="register.php" class="map-register-fab d-md-none" id="registerFab">
                <i data-lucide="plus" style="width:18px;height:18px;"></i>
                <span>Register Land</span>
            </a>

            <!-- Backdrop (mobile, dims map behind sheet) -->
            <div class="map-sheet-backdrop" id="mapSheetBackdrop" onclick="closeLandCardSheet()"></div>

            <!-- Bottom Sheet -->
            <div id="mobileLandCardSheet" class="mobile-land-sheet hidden">
                <!-- Color status strip -->
                <div id="sheetStatusStrip" class="sheet-status-strip" style="background:#198754;"></div>

                <!-- Drag handle -->
                <div class="sheet-drag-handle"></div>

                <!-- Header row -->
                <div class="d-flex align-items-start justify-content-between mb-2 px-1">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span id="sheetStatusBadge" class="badge rounded-pill text-white px-2 py-1"
                                style="font-size:0.68rem;background:#198754;">Approved</span>
                            <span id="sheetOwnerBadge" class="badge rounded-pill border text-secondary px-2 py-1"
                                style="font-size:0.68rem;background:#f8f9fa;">Owner</span>
                        </div>
                        <h3 id="sheetLandTitle" class="fw-bold text-dark mb-0" style="font-size:1rem;line-height:1.2;">
                            Land Title</h3>
                        <p id="sheetLandAddress" class="text-secondary mb-0 d-flex align-items-center gap-1 mt-1"
                            style="font-size:0.75rem;">
                            <i data-lucide="map-pin" style="width:12px;height:12px;"
                                class="text-success flex-shrink-0"></i>
                            Address here
                        </p>
                    </div>
                    <button type="button" class="btn-close ms-2 flex-shrink-0" style="font-size:0.7rem;"
                        onclick="closeLandCardSheet()"></button>
                </div>

                <!-- Stat tiles -->
                <div class="d-flex gap-2 mb-3 mt-1">
                    <div class="sheet-stat-tile">
                        <span class="stat-label">Area</span>
                        <span id="sheetLandArea" class="stat-value">— m²</span>
                    </div>
                    <div class="sheet-stat-tile">
                        <span class="stat-label">Plots</span>
                        <span id="sheetPlotsCount" class="stat-value" style="color:#198754;">— / —</span>
                    </div>
                    <div class="sheet-stat-tile">
                        <span class="stat-label">Crops</span>
                        <span id="sheetCropsCount" class="stat-value" style="font-size:0.72rem;color:#6c757d;">—</span>
                    </div>
                </div>

                <!-- Description -->
                <p id="sheetLandDesc" class="text-secondary mb-2"
                    style="font-size:0.78rem;line-height:1.5;max-height:48px;overflow:hidden;"></p>

                <!-- Partition Plots Grid -->
                <div class="mb-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-secondary fw-semibold" style="font-size:0.7rem;letter-spacing:0.5px;text-transform:uppercase;">Partition Plots Grid</span>
                        <span id="sheetPlotsSummary" class="badge bg-success-subtle text-success rounded-pill px-2 py-0.5" style="font-size:0.65rem;"></span>
                    </div>
                    <div id="sheetPlotsGrid" class="row g-2" style="max-height: 140px; overflow-y: auto;"></div>
                </div>

                <!-- CTA buttons -->
                <div class="d-flex gap-2">
                    <a id="sheetManageBtn" href="lands.php"
                        class="btn btn-drive-primary btn-sm rounded-pill flex-grow-1 d-flex align-items-center justify-content-center gap-1"
                        style="padding:10px;">
                        <i data-lucide="layers" style="width:14px;height:14px;"></i> Manage Plots
                    </a>
                    <a href="requests.php"
                        class="btn btn-drive-secondary btn-sm rounded-pill d-flex align-items-center gap-1 px-3"
                        style="padding:10px;">
                        <i data-lucide="file-text" style="width:14px;height:14px;"></i>
                        <span class="d-none d-sm-inline">Requests</span>
                    </a>
                    <a href="profile.php" class="btn btn-sm rounded-pill d-flex align-items-center gap-1 px-3"
                        style="padding:10px;background:#f0fdf4;border:1px solid #c3e6cb;color:#198754;">
                        <i data-lucide="user" style="width:14px;height:14px;"></i>
                    </a>
                </div>
            </div>

        </div>
    </div>
</main>

<script>
    const landsData = <?php echo json_encode($lands_data); ?>;
    const plotsData = <?php echo json_encode($plots_data); ?>;
    let landownerMap = null;
    let mapMarkers = [];
    let activePlotLayers = [];

    document.addEventListener('DOMContentLoaded', initLandownerMap);

    function initLandownerMap() {
        const el = document.getElementById('landownerMainMap');
        if (!el) return;

        landownerMap = L.map('landownerMainMap', { zoomControl: false })
            .setView([14.6010, 120.9890], 13);

        L.control.zoom({ position: 'topright' }).addTo(landownerMap);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap contributors'
        }).addTo(landownerMap);

        renderMapPins(landsData);

        // Check if URL has ?id=X parameter to focus & zoom into specific garden
        const urlParams = new URLSearchParams(window.location.search);
        const paramId = urlParams.get('id');
        if (paramId) {
            const targetLand = landsData.find(l => l.id == paramId);
            if (targetLand) {
                setTimeout(() => openLandCardSheet(targetLand), 300);
            }
        }

        // Hide register FAB when sheet is open
        landownerMap.on('click', closeLandCardSheet);
    }

    function renderMapPins(list) {
        if (!landownerMap) return;

        // Clear existing map markers and plot layers
        mapMarkers.forEach(m => landownerMap.removeLayer(m));
        mapMarkers = [];
        if (activePlotLayers) {
            activePlotLayers.forEach(l => landownerMap.removeLayer(l));
        }
        activePlotLayers = [];

        const bounds = [];

        list.forEach(land => {
            const lat = parseFloat(land.latitude);
            const lng = parseFloat(land.longitude);
            if (isNaN(lat) || isNaN(lng)) return;
            bounds.push([lat, lng]);

            const approved = land.status === 'approved';
            const pinColor = approved ? '#198754' : '#e8a000';

            // 1. Garden Perimeter Circle
            const baseRadius = Math.sqrt((parseFloat(land.area) || 100) / Math.PI);
            const gardenCircle = L.circle([lat, lng], {
                radius: baseRadius,
                color: pinColor,
                weight: 2,
                fillColor: pinColor,
                fillOpacity: 0.12,
                dashArray: '5, 5'
            }).addTo(landownerMap);
            mapMarkers.push(gardenCircle);

            // 2. Main Garden Pin
            const icon = L.divIcon({
                className: '',
                html: `<div class="land-map-pin" style="background:${pinColor};">
                       <i class="bi ${approved ? 'bi-tree-fill' : 'bi-clock-fill'}"></i>
                   </div>`,
                iconSize: [42, 42],
                iconAnchor: [21, 42]
            });

            const marker = L.marker([lat, lng], { icon }).addTo(landownerMap);
            marker.on('click', () => openLandCardSheet(land));
            mapMarkers.push(marker);

            // 3. Partition Plots Grid on Dashboard Map
            const landPlots = plotsData.filter(p => p.land_id == land.id);
            if (landPlots.length > 0) {
                landPlots.forEach((p, idx) => {
                    const angle = (idx / landPlots.length) * 2 * Math.PI;
                    const dist = baseRadius * 0.65;
                    const offsetLat = lat + ((dist * Math.sin(angle)) / 111320);
                    const offsetLng = lng + ((dist * Math.cos(angle)) / (111320 * Math.cos(lat * Math.PI / 180)));

                    const isAvail = p.status === 'available';
                    const plotBg = isAvail ? '#198754' : (p.status === 'occupied' ? '#0d6efd' : '#6c757d');

                    const plotPin = L.divIcon({
                        className: '',
                        html: `<div class="shadow-sm d-flex align-items-center justify-content-center fw-bold text-white rounded-circle" 
                            style="background:${plotBg};width:24px;height:24px;font-size:8.5px;border:2px solid #fff;box-shadow: 0 2px 5px rgba(0,0,0,0.25);">
                            ${p.plot_number.replace('Plot ', '')}
                        </div>`,
                        iconSize: [24, 24],
                        iconAnchor: [12, 12]
                    });

                    const plotMarker = L.marker([offsetLat, offsetLng], { icon: plotPin }).addTo(landownerMap);
                    plotMarker.bindTooltip(
                        `<div style="font-family:'Outfit',sans-serif;font-size:11px;">
                            <strong>${p.plot_number}</strong> (${p.area} m²)<br>
                            <span class="text-secondary">${land.title}</span><br>
                            <span class="badge ${isAvail ? 'bg-success' : 'bg-primary'}" style="font-size:9px;margin-top:2px;">${p.status}</span>
                        </div>`,
                        { permanent: false, direction: 'top' }
                    );
                    plotMarker.on('click', () => openLandCardSheet(land));
                    mapMarkers.push(plotMarker);
                });
            }
        });

        if (bounds.length) {
            landownerMap.fitBounds(bounds, { padding: [80, 80], maxZoom: 15 });
        }
    }

    function openLandCardSheet(land) {
        const sheet = document.getElementById('mobileLandCardSheet');
        const backdrop = document.getElementById('mapSheetBackdrop');
        const fab = document.getElementById('registerFab');
        if (!sheet) return;

        // Smooth zoom in to selected garden
        const lat = parseFloat(land.latitude);
        const lng = parseFloat(land.longitude);
        if (!isNaN(lat) && !isNaN(lng) && landownerMap) {
            landownerMap.flyTo([lat, lng], 17, {
                animate: true,
                duration: 1.0
            });
        }

        const approved = land.status === 'approved';
        const stripColor = approved ? '#198754' : '#ffc107';

        document.getElementById('sheetStatusStrip').style.background = stripColor;
        document.getElementById('sheetStatusBadge').textContent = approved ? 'Approved' : 'Pending Review';
        document.getElementById('sheetStatusBadge').style.background = stripColor;
        document.getElementById('sheetStatusBadge').style.color = approved ? '#fff' : '#333';
        document.getElementById('sheetOwnerBadge').textContent = land.landowner || 'Landowner';
        document.getElementById('sheetLandTitle').textContent = land.title;
        document.getElementById('sheetLandAddress').innerHTML =
            `<i data-lucide="map-pin" style="width:12px;height:12px;" class="text-success flex-shrink-0"></i> ${land.address}`;
        document.getElementById('sheetLandArea').textContent = `${parseFloat(land.area).toFixed(0)} m²`;
        document.getElementById('sheetPlotsCount').textContent = `${land.occupied_plots ?? 0} / ${land.total_plots ?? 0}`;
        const crops = Array.isArray(land.crops) ? land.crops.slice(0, 2).join(', ') : (land.crops || '—');
        document.getElementById('sheetCropsCount').textContent = crops;
        document.getElementById('sheetLandDesc').textContent = land.description || '';
        document.getElementById('sheetManageBtn').href = `lands.php?id=${land.id}`;

        // Render Partition Plots Grid in sheet
        const landPlots = plotsData.filter(p => p.land_id == land.id);
        const availPlots = landPlots.filter(p => p.status === 'available').length;
        const summaryBadge = document.getElementById('sheetPlotsSummary');
        if (summaryBadge) {
            summaryBadge.textContent = `${availPlots} / ${landPlots.length} Available`;
        }

        const plotsGridEl = document.getElementById('sheetPlotsGrid');
        if (plotsGridEl) {
            if (landPlots.length === 0) {
                plotsGridEl.innerHTML = `<div class="col-12 text-center text-muted py-2" style="font-size:0.75rem;">No partition plots configured yet.</div>`;
            } else {
                plotsGridEl.innerHTML = landPlots.map(p => {
                    const isAvail = p.status === 'available';
                    const isOccupied = p.status === 'occupied';
                    const bgClass = isAvail ? 'bg-success-subtle border-success-subtle text-success' : (isOccupied ? 'bg-primary-subtle border-primary-subtle text-primary' : 'bg-light border text-secondary');
                    const badgeText = isAvail ? 'Available' : (isOccupied ? 'Occupied' : 'Maintenance');
                    return `
                        <div class="col-6 col-sm-4">
                            <div class="p-2 rounded-3 border ${bgClass} d-flex flex-column justify-content-between h-100" style="font-size:0.75rem;">
                                <div class="fw-bold d-flex align-items-center justify-content-between">
                                    <span>${p.plot_number}</span>
                                    <span class="badge ${isAvail ? 'bg-success' : (isOccupied ? 'bg-primary' : 'bg-secondary')} rounded-circle" style="width:6px;height:6px;padding:0;"></span>
                                </div>
                                <div class="mt-1 d-flex justify-content-between align-items-center text-muted" style="font-size:0.68rem;">
                                    <span>${parseFloat(p.area).toFixed(0)} m²</span>
                                    <span class="fw-medium">${badgeText}</span>
                                </div>
                            </div>
                        </div>
                    `;
                }).join('');
            }
        }

        // Draw Plot Grid markers on Leaflet map around garden location
        if (activePlotLayers) {
            activePlotLayers.forEach(l => landownerMap.removeLayer(l));
        }
        activePlotLayers = [];

        if (landPlots.length > 0 && !isNaN(lat) && !isNaN(lng) && landownerMap) {
            const baseRadius = Math.sqrt((parseFloat(land.area) || 100) / Math.PI);

            // Outer boundary circle
            const gardenCircle = L.circle([lat, lng], {
                radius: baseRadius,
                color: '#198754',
                weight: 2.5,
                fillColor: '#198754',
                fillOpacity: 0.12,
                dashArray: '5, 5'
            }).addTo(landownerMap);
            activePlotLayers.push(gardenCircle);

            // Plot markers grid
            landPlots.forEach((p, idx) => {
                const angle = (idx / landPlots.length) * 2 * Math.PI;
                const dist = baseRadius * 0.55;
                const offsetLat = lat + ((dist * Math.sin(angle)) / 111320);
                const offsetLng = lng + ((dist * Math.cos(angle)) / (111320 * Math.cos(lat * Math.PI / 180)));

                const isAvail = p.status === 'available';
                const plotPin = L.divIcon({
                    className: '',
                    html: `<div class="shadow-sm d-flex align-items-center justify-content-center fw-bold text-white rounded-circle" 
                        style="background:${isAvail ? '#198754' : '#0d6efd'};width:26px;height:26px;font-size:9px;border:2px solid #fff;">
                        ${p.plot_number.replace('Plot ', '')}
                    </div>`,
                    iconSize: [26, 26],
                    iconAnchor: [13, 13]
                });

                const plotMarker = L.marker([offsetLat, offsetLng], { icon: plotPin }).addTo(landownerMap);
                plotMarker.bindTooltip(`<b>${p.plot_number}</b> (${p.area} m²)<br><span class="text-capitalize">${p.status}</span>`, { permanent: false, direction: 'top' });
                activePlotLayers.push(plotMarker);
            });
        }

        sheet.classList.remove('hidden');
        if (backdrop) backdrop.classList.add('show');
        if (fab) fab.style.display = 'none';
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function closeLandCardSheet() {
        const sheet = document.getElementById('mobileLandCardSheet');
        const backdrop = document.getElementById('mapSheetBackdrop');
        const fab = document.getElementById('registerFab');
        if (sheet) sheet.classList.add('hidden');
        if (backdrop) backdrop.classList.remove('show');
        if (fab) fab.style.display = '';

        if (activePlotLayers) {
            activePlotLayers.forEach(l => landownerMap.removeLayer(l));
            activePlotLayers = [];
        }
    }

    function recenterLandownerMap() {
        renderMapPins(landsData);
        closeLandCardSheet();
    }

    function filterMapLands(type, btn) {
        document.querySelectorAll('.map-chip').forEach(c => c.classList.remove('active'));
        if (btn) btn.classList.add('active');
        const map = { my: l => l.landowner === 'John Landowner', approved: l => l.status === 'approved', pending: l => l.status === 'pending' };
        renderMapPins(type === 'all' ? landsData : landsData.filter(map[type]));
        closeLandCardSheet();
    }
</script>

<?php include '../includes/footer.php'; ?>