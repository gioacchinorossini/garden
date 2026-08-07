<?php
$base_path = '../';
$page_title = "Gardening Map";
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<main class="workspace-surface">
    <!-- Toolbar/Title Bar -->
    <div class="toolbar border-bottom">
        <div>
            <h1 class="fs-5 fw-semibold m-0 text-dark">Community Gardening Map</h1>
            <p class="text-muted mb-0" style="font-size: 0.75rem;">Explore nearby idle properties and visual land partitions geographically</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="browse.php" class="btn btn-outline-secondary rounded-pill btn-sm px-3">
                <i class="bi bi-grid-fill me-1 text-success"></i> Grid View
            </a>
        </div>
    </div>

    <!-- Workspace Area (No Scroll, Full Map) -->
    <div class="flex-grow-1 position-relative" style="height: 100%;">
        <div id="gardenerMap" style="width: 100%; height: 100%; min-height: 500px; background-color: #e9f2ff;"></div>
    </div>
</main>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Initializing map centered at center Manila/Manila Bay coordinates
        const map = L.map('gardenerMap').setView([14.5995, 120.9842], 13);
        
        // Load OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Fetch mock lands list
        const lands = <?php echo json_encode($_SESSION['mock_lands'] ?? []); ?>;
        
        lands.forEach(function(land) {
            if (land.status === 'approved') {
                // Pin color matching primary gardening green
                const color = '#198754'; 
                
                const customIcon = L.divIcon({
                    className: 'custom-icon',
                    html: `<div style="background-color: ${color}; width: 16px; height: 16px; border: 2px solid white; border-radius: 50%; box-shadow: 0 1px 4px rgba(0,0,0,0.4)"></div>`,
                    iconSize: [16, 16]
                });

                L.marker([parseFloat(land.latitude), parseFloat(land.longitude)], { icon: customIcon })
                    .addTo(map)
                    .bindPopup(`
                        <div style="font-family: 'Outfit', sans-serif; min-width: 180px;">
                            <strong style="font-size:13px; color: #191c1a; display:block; margin-bottom: 4px;">${land.title}</strong>
                            <span style="font-size:11px; color:#707973; display:block; margin-bottom: 8px;"><i class="bi bi-geo-alt-fill"></i> ${land.address}</span>
                            <span style="font-size:11px; display:block; margin-bottom: 8px;"><strong>Size:</strong> ${land.area} m²</span>
                            <a href="browse.php" class="btn btn-sm btn-success w-100 text-white" style="font-size: 10px; border-radius: 4px; padding: 2px 8px; font-weight:500;">View Available Plots</a>
                        </div>
                    `);
            }
        });
    });
</script>

<?php include '../includes/footer.php'; ?>
