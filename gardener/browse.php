<?php
$base_path = '../';
$page_title = "Browse Lands";
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ensure mock lands exists in session
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
            'description' => 'A spacious lot with fertile soil and partial shade, perfect for root vegetables.'
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
            'description' => 'An elevated deck prepared with planters and drip irrigation, ideal for leafy greens and herbs.'
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
            'description' => 'Large idle pasture near the riverbed. High soil quality, direct sunlight access.'
        ]
    ];
}

// Ensure mock plots exists in session
if (!isset($_SESSION['mock_plots'])) {
    $_SESSION['mock_plots'] = [
        ['id' => 1, 'land_id' => 2, 'plot_number' => 'Plot A-1', 'area' => 20.0, 'status' => 'occupied'],
        ['id' => 2, 'land_id' => 2, 'plot_number' => 'Plot A-2', 'area' => 20.0, 'status' => 'occupied'],
        ['id' => 3, 'land_id' => 2, 'plot_number' => 'Plot B-1', 'area' => 22.0, 'status' => 'available'],
        ['id' => 4, 'land_id' => 2, 'plot_number' => 'Plot B-2', 'area' => 23.5, 'status' => 'available'],
        ['id' => 5, 'land_id' => 3, 'plot_number' => 'Plot R-1', 'area' => 100.0, 'status' => 'available']
    ];
}

// Handle request submission
$success_msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_request') {
    $land_id = intval($_POST['land_id']);
    $plot_id = intval($_POST['plot_id']);
    $purpose = htmlspecialchars(trim($_POST['purpose']));
    $duration = htmlspecialchars(trim($_POST['duration']));

    // Find land title
    $land_title = "";
    foreach ($_SESSION['mock_lands'] as $land) {
        if ($land['id'] === $land_id) {
            $land_title = $land['title'];
            break;
        }
    }

    // Find plot number
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
    $success_msg = "Plot application submitted successfully! Tracking is available under 'My Requests'.";
}

function get_land_image_url($id)
{
    $images = [
        1 => 'https://images.unsplash.com/photo-1466692476868-aef1dfb1e735?auto=format&fit=crop&q=80&w=400',
        2 => 'https://images.unsplash.com/photo-1530595467537-0b5996c41f2d?auto=format&fit=crop&q=80&w=400',
        3 => 'https://images.unsplash.com/photo-1500937386664-56d1dfef3854?auto=format&fit=crop&q=80&w=400'
    ];
    return $images[$id] ?? 'https://images.unsplash.com/photo-1466692476868-aef1dfb1e735?auto=format&fit=crop&q=80&w=400';
}
?>


<main class="workspace-surface">
    <!-- Toolbar/Title Bar -->
    <div class="toolbar border-bottom">
        <div>
            <h1 class="fs-5 fw-semibold m-0 text-dark">Available Lands</h1>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="map.php" class="btn btn-outline-secondary rounded-pill btn-sm px-3">
                <i class="bi bi-map-fill me-1 text-success"></i> View Map
            </a>
        </div>
    </div>

    <!-- Workspace Scrollable Area -->
    <div class="workspace-scroll">
        <?php if (!empty($success_msg)): ?>
            <div class="alert alert-success d-flex align-items-center gap-2 border-0  mb-4" role="alert"
                style="border-radius: 12px; background-color: #d1e7dd; color: #0f5132;">
                <i class="bi bi-check-circle-fill fs-5"></i>
                <div><?php echo $success_msg; ?></div>
            </div>
        <?php endif; ?>

        <!-- Lands Cards Grid -->
        <div class="row g-4">
            <?php
            $approved_lands = array_filter($_SESSION['mock_lands'], function ($l) {
                return $l['status'] === 'approved';
            });
            ?>

            <?php if (empty($approved_lands)): ?>
                <div class="col-12 text-center py-5 text-muted">
                    <i class="bi bi-tree fs-1 d-block mb-2"></i>
                    <span>No approved lands available for community gardening right now.</span>
                </div>
            <?php else: ?>
                <?php foreach ($approved_lands as $land): ?>
                    <?php
                    $land_plots = array_filter($_SESSION['mock_plots'], function ($p) use ($land) {
                        return $p['land_id'] == $land['id'];
                    });
                    $available_count = count(array_filter($land_plots, fn($p) => $p['status'] === 'available'));
                    $total_count = count($land_plots);
                    ?>
                    <div class="col-md-6 col-xl-4">
                        <div class="land-listing-card rounded-4 overflow-hidden border h-100 d-flex flex-column">
                            <!-- ① Hero Image with toggle overlay -->
                            <div class="position-relative" style="height: 200px; overflow: hidden;">
                                <!-- Map Preview (shown by default) -->
                                <div id="map-preview-<?php echo $land['id']; ?>" style="height: 200px; z-index: 1;"></div>
                                <!-- Image (hidden by default) -->
                                <div id="image-preview-<?php echo $land['id']; ?>" class="d-none"
                                    style="height:200px; overflow:hidden;">
                                    <img src="<?php echo get_land_image_url($land['id']); ?>"
                                        alt="<?php echo htmlspecialchars($land['title']); ?>" class="w-100 h-100"
                                        style="object-fit: cover;">
                                </div>

                                <!-- Toggle pill -->
                                <button type="button"
                                    class="btn btn-sm rounded-pill  d-flex align-items-center gap-1 position-absolute"
                                    style="bottom: 10px; left: 10px; z-index: 10; font-size: 11px; padding: 4px 12px; background: rgba(255,255,255,0.92); border: 1px solid rgba(255,255,255,0.6); backdrop-filter: blur(4px);"
                                    onclick="toggleMediaPreview(<?php echo $land['id']; ?>, this)">
                                    <i class="bi bi-image text-success"></i>
                                    <span>View Photo</span>
                                </button>

                                <!-- Approved badge -->
                                <span class="badge position-absolute"
                                    style="top: 12px; right: 12px; font-size: 10px; padding: 5px 10px; background: rgba(25,135,84,0.15); color: #0f5132; border: 1px solid rgba(25,135,84,0.25); backdrop-filter: blur(4px);">
                                    ✓ Approved
                                </span>
                            </div>

                            <!-- ② Card Body -->
                            <div class="p-3 pb-2">
                                <!-- Title -->
                                <h3 class="fw-bold text-dark mb-0" style="font-size: 1.05rem; line-height: 1.3;">
                                    <?php echo htmlspecialchars($land['title']); ?>
                                </h3>

                                <!-- Location -->
                                <p class="text-muted mb-2 mt-1" style="font-size: 0.78rem;">
                                    <i class="bi bi-geo-alt-fill me-1" style="color: var(--drive-primary);"></i>
                                    <?php echo htmlspecialchars($land['address']); ?>
                                </p>

                                <!-- Feature Chips Row -->
                                <div class="d-flex gap-3 py-2 mb-2"
                                    style="border-top: 1px solid var(--drive-border); border-bottom: 1px solid var(--drive-border);">
                                    <div class="text-center flex-fill">
                                        <i class="bi bi-tree d-block mb-1"
                                            style="font-size: 1.2rem; color: var(--drive-primary);"></i>
                                        <span style="font-size: 0.7rem; color: #6c757d;">Garden</span>
                                    </div>
                                    <div class="text-center flex-fill">
                                        <i class="bi bi-grid-3x3-gap d-block mb-1"
                                            style="font-size: 1.2rem; color: var(--drive-primary);"></i>
                                        <span style="font-size: 0.7rem; color: #6c757d;"><?php echo $total_count; ?>
                                            plots</span>
                                    </div>
                                    <div class="text-center flex-fill">
                                        <i class="bi bi-check2-square d-block mb-1"
                                            style="font-size: 1.2rem; color: var(--drive-primary);"></i>
                                        <span style="font-size: 0.7rem; color: #6c757d;"><?php echo $available_count; ?>
                                            free</span>
                                    </div>
                                    <div class="text-center flex-fill">
                                        <i class="bi bi-rulers d-block mb-1"
                                            style="font-size: 1.2rem; color: var(--drive-primary);"></i>
                                        <span
                                            style="font-size: 0.7rem; color: #6c757d;"><?php echo number_format($land['area'], 0); ?>
                                            m²</span>
                                    </div>
                                </div>

                                <!-- ③ Collapsible Description -->
                                <div class="mb-2">
                                    <p class="text-secondary mb-0 land-desc-text" id="desc-text-<?php echo $land['id']; ?>"
                                        style="font-size: 0.82rem; line-height: 1.6; display: none;">
                                        <?php echo htmlspecialchars($land['description']); ?>
                                    </p>
                                    <button class="btn btn-link p-0 mt-1 desc-toggle"
                                        style="font-size: 0.75rem; color: var(--drive-primary); font-weight: 500; text-decoration: none;"
                                        onclick="toggleDesc(<?php echo $land['id']; ?>, this)">
                                        Description <i class="bi bi-chevron-down" style="font-size: 0.65rem;"></i>
                                    </button>
                                </div>

                                <!-- ④ Landowner info -->
                                <div class="d-flex align-items-center gap-2 mb-3" style="font-size: 0.75rem; color: #6c757d;">
                                    <div class="rounded-circle bg-success-subtle d-flex align-items-center justify-content-center"
                                        style="width: 24px; height: 24px; min-width:24px;">
                                        <i class="bi bi-person-fill text-success" style="font-size: 0.7rem;"></i>
                                    </div>
                                    <span><?php echo htmlspecialchars($land['landowner']); ?></span>
                                </div>

                                <!-- ⑤ Partitioned Plots -->
                                <?php if (!empty($land_plots)): ?>
                                    <div class="mb-1">
                                        <p class="fw-semibold text-secondary mb-2"
                                            style="font-size: 0.7rem; letter-spacing: 0.5px; text-transform: uppercase;">Available
                                            Plots</p>
                                        <div class="row g-2">
                                            <?php foreach ($land_plots as $plot): ?>
                                                <div class="col-3">
                                                    <div class="p-2 rounded-3 text-center d-flex flex-column align-items-center justify-content-between"
                                                        style="font-size: 0.7rem; min-height: 80px; border: 1px solid var(--drive-border); background: <?php echo $plot['status'] === 'available' ? '#f0fdf6' : '#f8f9fa'; ?>;">
                                                        <span class="fw-semibold text-dark text-truncate w-100"
                                                            style="font-size: 0.72rem;"><?php echo htmlspecialchars($plot['plot_number']); ?></span>
                                                        <span class="text-muted d-block"
                                                            style="font-size: 0.65rem;"><?php echo $plot['area']; ?> m²</span>
                                                        <?php if ($plot['status'] === 'available'): ?>
                                                            <button class="btn btn-drive-primary py-1 px-1 w-100 mt-1"
                                                                style="font-size: 9px;" data-bs-toggle="modal"
                                                                data-bs-target="#requestPlotModal<?php echo $plot['id']; ?>">
                                                                Request
                                                            </button>
                                                        <?php else: ?>
                                                            <span class="badge rounded-pill mt-1 w-100"
                                                                style="font-size: 7.5px; padding: 3px 4px; background: rgba(108,117,125,0.1); color: #6c757d;">
                                                                <?php echo ucfirst($plot['status']); ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>

                                                <!-- Plot Request Modal -->
                                                <div class="modal fade" id="requestPlotModal<?php echo $plot['id']; ?>" tabindex="-1"
                                                    aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content drive-modal-content">
                                                            <div class="modal-header border-0 pb-0">
                                                                <h5 class="modal-title fw-semibold text-dark">Apply for Plot</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                    aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body text-start">
                                                                <form action="browse.php" method="POST">
                                                                    <input type="hidden" name="action" value="submit_request">
                                                                    <input type="hidden" name="land_id"
                                                                        value="<?php echo $land['id']; ?>">
                                                                    <input type="hidden" name="plot_id"
                                                                        value="<?php echo $plot['id']; ?>">

                                                                    <div class="p-3 bg-light rounded-3 mb-3" style="font-size: 0.8rem;">
                                                                        <strong>Land:</strong>
                                                                        <?php echo htmlspecialchars($land['title']); ?><br>
                                                                        <strong>Selected Plot:</strong>
                                                                        <?php echo htmlspecialchars($plot['plot_number']); ?>
                                                                        (<?php echo $plot['area']; ?> m²)
                                                                    </div>

                                                                    <div class="mb-3">
                                                                        <label for="duration" class="form-label text-secondary"
                                                                            style="font-size: 0.75rem; font-weight:600;">DURATION</label>
                                                                        <select class="form-select drive-form-control w-100"
                                                                            id="duration" name="duration" required>
                                                                            <option value="3 Months">3 Months</option>
                                                                            <option value="6 Months">6 Months</option>
                                                                            <option value="1 Year">1 Year</option>
                                                                            <option value="Indefinite">Indefinite</option>
                                                                        </select>
                                                                    </div>

                                                                    <div class="mb-4">
                                                                        <label for="purpose" class="form-label text-secondary"
                                                                            style="font-size: 0.75rem; font-weight:600;">PURPOSE</label>
                                                                        <textarea class="form-control drive-form-control w-100"
                                                                            id="purpose" name="purpose" rows="3" required
                                                                            placeholder="Describe your gardening plans..."></textarea>
                                                                    </div>

                                                                    <div class="d-flex justify-content-end gap-2">
                                                                        <button type="button" class="btn btn-drive-secondary"
                                                                            data-bs-dismiss="modal">Cancel</button>
                                                                        <button type="submit" class="btn btn-drive-primary">Submit</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted mb-2" style="font-size: 0.78rem;"><i class="bi bi-info-circle me-1"></i>No
                                        plots partitioned yet.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<script>
    const _leafletMaps = {};

    document.addEventListener("DOMContentLoaded", function () {
        <?php foreach ($approved_lands as $land): ?>
                (function () {
                    const mapId = 'map-preview-<?php echo $land['id']; ?>';
                    const lat = <?php echo floatval($land['latitude']); ?>;
                    const lng = <?php echo floatval($land['longitude']); ?>;
                    const area = <?php echo floatval($land['area']); ?>; // m²
                    const el = document.getElementById(mapId);
                    if (!el || el._leaflet_id) return;

                    const map = L.map(mapId, {
                        zoomControl: false, dragging: false, touchZoom: false,
                        doubleClickZoom: false, scrollWheelZoom: false,
                        boxZoom: false, keyboard: false, attributionControl: false
                    }).setView([lat, lng], 17);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);

                    // Radius = sqrt(area / π) — circle with same m² area as the land
                    const radius = Math.sqrt(area / Math.PI);

                    L.circle([lat, lng], {
                        radius: radius,
                        color: '#198754',
                        weight: 2,
                        fillColor: '#198754',
                        fillOpacity: 0.15
                    }).addTo(map);

                    // Center pin
                    const pin = L.divIcon({
                        className: '',
                        html: `<div style="background:#198754;width:10px;height:10px;border:2.5px solid #fff;border-radius:50%;margin:-5px 0 0 -5px;"></div>`,
                        iconSize: [0, 0]
                    });
                    L.marker([lat, lng], { icon: pin }).addTo(map);

                    _leafletMaps[mapId] = map;
                })();
        <?php endforeach; ?>
    });

    function toggleMediaPreview(landId, btn) {
        const mapEl = document.getElementById(`map-preview-${landId}`);
        const imgEl = document.getElementById(`image-preview-${landId}`);
        const text = btn.querySelector('span');
        const icon = btn.querySelector('i');
        const showingMap = !mapEl.classList.contains('d-none');
        if (showingMap) {
            // Switch to Image
            mapEl.classList.add('d-none');
            imgEl.classList.remove('d-none');
            text.innerText = 'View Map';
            icon.className = 'bi bi-map text-success';
        } else {
            // Switch to Map
            imgEl.classList.add('d-none');
            mapEl.classList.remove('d-none');
            text.innerText = 'View Photo';
            icon.className = 'bi bi-image text-success';
            // Invalidate map size so tiles render correctly after being hidden
            const mapInstance = _leafletMaps[`map-preview-${landId}`];
            if (mapInstance) setTimeout(() => mapInstance.invalidateSize(), 50);
        }
    }

    function toggleDesc(landId, btn) {
        const p = document.getElementById(`desc-text-${landId}`);
        const isHidden = p.style.display === 'none';
        if (isHidden) {
            p.style.display = 'block';
            btn.innerHTML = 'Hide description <i class="bi bi-chevron-up" style="font-size:.65rem;"></i>';
        } else {
            p.style.display = 'none';
            btn.innerHTML = 'Description <i class="bi bi-chevron-down" style="font-size:.65rem;"></i>';
        }
    }
</script>

<?php include '../includes/footer.php'; ?>