document.addEventListener("DOMContentLoaded", function() {
    let landsList = [];
    let activeFilter = 'all'; // all, pending, approved

    // DOM Elements
    const landsTableBody = document.getElementById('landsTableBody');
    const filterButtons = document.querySelectorAll('.filter-btn');
    const landsCountText = document.getElementById('landsCountText');
    const mapWrapper = document.getElementById('mapWrapper');

    // Modals and Forms
    const viewLandModalEl = document.getElementById('viewLandModal');
    const rejectModalEl = document.getElementById('rejectModal');
    const rejectForm = document.getElementById('rejectForm');

    // Map instances
    let map;
    let markersLayer;

    // Load lands
    async function loadLands() {
        try {
            const response = await fetch('../api/lands.php');
            const res = await response.json();
            if (res.status === 'success') {
                landsList = res.data.lands;
                
                // Initialize map if not yet done
                initMap();
                
                // Render list & update map markers
                renderLands();
            } else {
                console.error("API error:", res.message);
            }
        } catch (err) {
            console.error("Failed to load lands:", err);
        }
    }

    // Initialize Map
    function initMap() {
        if (map) return;
        
        map = L.map('landMap').setView([14.5995, 120.9842], 13);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        markersLayer = L.layerGroup().addTo(map);
    }

    // Update map markers based on filtered list
    function updateMapMarkers(filteredLands) {
        if (!markersLayer) return;
        markersLayer.clearLayers();

        filteredLands.forEach(land => {
            let color = 'orange';
            if (land.status === 'approved') color = 'green';
            if (land.status === 'rejected') color = 'red';

            const customIcon = L.divIcon({
                className: 'custom-icon',
                html: `<div style="background-color: ${color}; width: 14px; height: 14px; border: 2px solid white; border-radius: 50%; box-shadow: 0 1px 3px rgba(0,0,0,0.4)"></div>`,
                iconSize: [14, 14]
            });

            L.marker([parseFloat(land.latitude), parseFloat(land.longitude)], { icon: customIcon })
                .addTo(markersLayer)
                .bindPopup(`<strong>${land.title}</strong><br>${land.address}<br>Status: <span class="text-uppercase">${land.status}</span>`);
        });
    }

    // Render Lands list
    function renderLands() {
        const filtered = landsList.filter(land => {
            if (activeFilter === 'pending') return land.status === 'pending';
            if (activeFilter === 'approved') return land.status === 'approved';
            return true;
        });

        // Update count text
        if (landsCountText) {
            landsCountText.innerHTML = `Showing <strong>${filtered.length}</strong> of <strong>${landsList.length}</strong> submissions`;
        }

        updateMapMarkers(filtered);

        if (filtered.length === 0) {
            landsTableBody.innerHTML = `
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-folder-x fs-1 d-block mb-2"></i>
                    <span>No land submissions found.</span>
                </div>
            `;
            return;
        }

        landsTableBody.innerHTML = filtered.map(land => {
            let statusBadge = '';
            if (land.status === 'approved') {
                statusBadge = `<span class="badge bg-success rounded-pill" style="font-size: 10px;">Approved</span>`;
            } else if (land.status === 'pending') {
                statusBadge = `<span class="badge bg-warning text-dark rounded-pill" style="font-size: 10px;">Pending</span>`;
            } else {
                statusBadge = `<span class="badge bg-danger rounded-pill" style="font-size: 10px;" title="Reason: ${land.reason}">Rejected</span>`;
            }

            let moderationButtons = `
                <button onclick="openViewModal(${land.id})" class="btn icon-btn-pill" title="View details">
                    <i class="bi bi-eye text-primary"></i>
                </button>
            `;

            if (land.status === 'pending') {
                moderationButtons += `
                    <button onclick="approveLand(${land.id})" class="btn icon-btn-pill" title="Approve Registration">
                        <i class="bi bi-check-circle-fill text-success"></i>
                    </button>
                    <button onclick="openRejectModal(${land.id})" class="btn icon-btn-pill" title="Reject Registration">
                        <i class="bi bi-x-circle-fill text-danger"></i>
                    </button>
                `;
            }

            return `
                <div class="d-flex align-items-center justify-content-between px-4 py-2 border-bottom hover:bg-[#f8faff] text-sm text-dark transition-colors" style="min-height: 64px;">
                    <!-- Land Details -->
                    <div class="w-25">
                        <div class="fw-semibold text-dark">${land.title}</div>
                        <small class="text-secondary truncate d-block" style="font-size: 0.75rem;"><i class="bi bi-geo-alt-fill text-muted me-1"></i>${land.address}</small>
                    </div>

                    <!-- Landowner -->
                    <div class="w-20 text-secondary">${land.landowner}</div>

                    <!-- Area -->
                    <div class="w-15 text-dark fw-medium">${parseFloat(land.area).toFixed(1)} m²</div>

                    <!-- Coords -->
                    <div class="w-15">
                        <code class="text-secondary" style="font-size: 0.75rem;">${land.latitude}, ${land.longitude}</code>
                    </div>

                    <!-- Status -->
                    <div class="w-10">${statusBadge}</div>

                    <!-- Action buttons -->
                    <div class="w-15 text-end d-flex align-items-center justify-content-end gap-1">
                        ${moderationButtons}
                    </div>
                </div>
            `;
        }).join('');
    }

    // Toggle Filters
    filterButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            filterButtons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            activeFilter = btn.dataset.filter;
            renderLands();
        });
    });

    // Toggle Map
    window.toggleMapSection = function() {
        mapWrapper.style.display = mapWrapper.style.display === 'none' ? 'block' : 'none';
        if (mapWrapper.style.display === 'block' && map) {
            setTimeout(() => { map.invalidateSize(); }, 200);
        }
    };

    // Approve Land
    window.approveLand = async function(id) {
        if (!confirm("Are you sure you want to approve this land submission?")) return;

        try {
            const response = await fetch('../api/lands.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'update_status',
                    status: 'approved',
                    id
                })
            });
            const res = await response.json();
            if (res.status === 'success') {
                loadLands();
            } else {
                alert("Error: " + res.message);
            }
        } catch (err) {
            console.error("Approve land failed:", err);
        }
    };

    // Open View Modal
    window.openViewModal = function(id) {
        const land = landsList.find(l => l.id === id);
        if (!land) return;

        document.getElementById('modal_title').innerText = land.title;
        document.getElementById('modal_description').innerText = land.description;
        document.getElementById('modal_owner').innerText = land.landowner;
        document.getElementById('modal_area').innerText = parseFloat(land.area).toFixed(1) + ' m²';
        document.getElementById('modal_address').innerText = land.address;
        document.getElementById('modal_coords').innerText = `${land.latitude}, ${land.longitude}`;

        const reasonBlock = document.getElementById('modal_reason_block');
        if (land.status === 'rejected' && land.reason) {
            reasonBlock.classList.remove('hidden');
            document.getElementById('modal_reason').innerText = land.reason;
        } else {
            reasonBlock.classList.add('hidden');
        }

        const modal = new bootstrap.Modal(viewLandModalEl);
        modal.show();
    };

    // Open Reject Modal
    window.openRejectModal = function(id) {
        document.getElementById('reject_land_id').value = id;
        document.getElementById('rejection_reason').value = '';

        const modal = new bootstrap.Modal(rejectModalEl);
        modal.show();
    };

    // Submit Reject Form
    if (rejectForm) {
        rejectForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const payload = {
                action: 'update_status',
                status: 'rejected',
                id: parseInt(document.getElementById('reject_land_id').value),
                reason: document.getElementById('rejection_reason').value
            };

            try {
                const response = await fetch('../api/lands.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const res = await response.json();
                if (res.status === 'success') {
                    const modal = bootstrap.Modal.getInstance(rejectModalEl);
                    if (modal) modal.hide();
                    rejectForm.reset();
                    loadLands();
                } else {
                    alert("Error: " + res.message);
                }
            } catch (err) {
                console.error("Reject land failed:", err);
            }
        });
    }

    // Initial load
    loadLands();
});
