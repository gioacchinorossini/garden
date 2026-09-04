document.addEventListener("DOMContentLoaded", function () {
    let landsList = [];
    let plotsList = [];
    let selectedLandId = null;
    const _leafletMapsOwner = {};

    // DOM Elements
    const landsContainer = document.getElementById('landsContainer');
    const modalPlotsContainer = document.getElementById('modalPlotsContainer');
    let managePlotsModalInstance = null;

    // Modals & Forms
    const editLandModalEl = document.getElementById('editLandModal');
    const editLandForm = document.getElementById('editLandForm');
    const addPlotModalEl = document.getElementById('addPlotModal');
    const addPlotForm = document.getElementById('addPlotForm');
    const managePlotsModalEl = document.getElementById('managePlotsModal');

    function getLandImageUrl(id) {
        const images = {
            1: 'https://images.unsplash.com/photo-1466692476868-aef1dfb1e735?auto=format&fit=crop&q=80&w=400',
            2: 'https://images.unsplash.com/photo-1530595467537-0b5996c41f2d?auto=format&fit=crop&q=80&w=400',
            3: 'https://images.unsplash.com/photo-1500937386664-56d1dfef3854?auto=format&fit=crop&q=80&w=400',
            4: 'https://images.unsplash.com/photo-1592417817098-8f3d6ef23a28?auto=format&fit=crop&q=80&w=400'
        };
        return images[id] || 'https://images.unsplash.com/photo-1466692476868-aef1dfb1e735?auto=format&fit=crop&q=80&w=400';
    }

    // Load lands & plots
    async function loadData() {
        try {
            const response = await fetch('../api/lands.php');
            const res = await response.json();
            if (res.status === 'success') {
                landsList = res.data.lands;
                plotsList = res.data.plots;

                renderLands();

                // Check URL parameter for land selection (e.g. ?id=2)
                const urlParams = new URLSearchParams(window.location.search);
                const paramLandId = urlParams.get('id');
                if (paramLandId) {
                    const matchLand = landsList.find(l => l.id == paramLandId);
                    if (matchLand && matchLand.status === 'approved') {
                        openManagePlotsModal(matchLand.id, matchLand.title);
                    }
                } else if (selectedLandId) {
                    renderPlotsModal(selectedLandId);
                }
            } else {
                console.error("API error:", res.message);
            }
        } catch (err) {
            console.error("Failed to load lands data:", err);
        }
    }

    // Render properties in a responsive full-width grid matching browse lands
    function renderLands() {
        if (!landsContainer) return;

        if (landsList.length === 0) {
            landsContainer.innerHTML = `
                <div class="col-12 text-center py-5 text-muted bg-white border rounded-4">
                    <i class="bi bi-folder-x fs-1 d-block mb-2"></i>
                    <span>No registered properties found.</span>
                </div>
            `;
            return;
        }

        landsContainer.innerHTML = landsList.map(land => {
            const landPlots = plotsList.filter(p => p.land_id == land.id);
            const totalCount = landPlots.length;
            const availableCount = landPlots.filter(p => p.status === 'available').length;

            let statusBadge = '';
            if (land.status === 'approved') {
                statusBadge = `<span class="badge position-absolute" style="top: 12px; right: 12px; font-size: 10px; padding: 5px 10px; background: rgba(25,135,84,0.15); color: #0f5132; border: 1px solid rgba(25,135,84,0.25); backdrop-filter: blur(4px);">✓ Approved</span>`;
            } else if (land.status === 'pending') {
                statusBadge = `<span class="badge position-absolute" style="top: 12px; right: 12px; font-size: 10px; padding: 5px 10px; background: rgba(255,193,7,0.2); color: #8a6700; border: 1px solid rgba(255,193,7,0.3); backdrop-filter: blur(4px);">⏳ Pending Review</span>`;
            } else {
                statusBadge = `<span class="badge position-absolute" style="top: 12px; right: 12px; font-size: 10px; padding: 5px 10px; background: rgba(220,53,69,0.15); color: #842029; border: 1px solid rgba(220,53,69,0.25); backdrop-filter: blur(4px);">✕ Rejected</span>`;
            }

            const isSelected = selectedLandId == land.id;
            const cardBorderClass = isSelected ? 'border-success shadow-sm' : '';

            const managePlotsBtn = (land.status === 'approved')
                ? `<button class="btn btn-sm btn-drive-primary rounded-pill px-3" onclick="openManagePlotsModal(${land.id}, '${land.title.replace(/'/g, "\\'")}')">Manage Plots <i class="bi bi-arrow-right ms-1"></i></button>`
                : `<button class="btn btn-sm btn-drive-secondary rounded-pill px-3" disabled>Plots locked</button>`;

            return `
                <div class="col-md-6 col-xl-4">
                    <div class="land-listing-card rounded-4 overflow-hidden border bg-white h-100 d-flex flex-column ${cardBorderClass}">
                        <!-- ① Hero Image / Mini Map preview with zoom-in click -->
                        <div class="position-relative" style="height: 200px; overflow: hidden; cursor: pointer;" onclick="zoomInCardMap(${land.id})" title="Click to zoom map">
                            <!-- Map Preview -->
                            <div id="map-preview-owner-${land.id}" style="height: 200px; z-index: 1;"></div>
                            <!-- Image Preview -->
                            <div id="image-preview-owner-${land.id}" class="d-none" style="height: 200px; overflow: hidden;">
                                <img src="${getLandImageUrl(land.id)}" alt="${land.title}" class="w-100 h-100" style="object-fit: cover;">
                            </div>

                            <!-- Toggle media pill -->
                            <button type="button"
                                class="btn btn-sm rounded-pill d-flex align-items-center gap-1 position-absolute"
                                style="bottom: 10px; left: 10px; z-index: 10; font-size: 11px; padding: 4px 12px; background: rgba(255,255,255,0.92); border: 1px solid rgba(255,255,255,0.6); backdrop-filter: blur(4px);"
                                onclick="event.stopPropagation(); toggleMediaPreviewOwner(${land.id}, this)">
                                <i class="bi bi-image text-success"></i>
                                <span>View Photo</span>
                            </button>

                            <!-- Status badge -->
                            ${statusBadge}
                        </div>

                        <div class="p-3 pb-2 flex-grow-1 d-flex flex-column justify-content-between">
                            <div>
                                <h3 class="fw-bold text-dark mb-0" style="font-size: 1.05rem; line-height: 1.3;">
                                    <a href="dashboard.php?id=${land.id}" class="text-dark text-decoration-none hover-text-success transition-colors">
                                        ${land.title}
                                    </a>
                                </h3>

                                <p class="text-muted mb-2 mt-1" style="font-size: 0.78rem;">
                                    <i class="bi bi-geo-alt-fill me-1" style="color: var(--drive-primary);"></i>
                                    ${land.address}
                                </p>

                                <!-- Feature Chips Row -->
                                <div class="d-flex gap-3 py-2 mb-2" style="border-top: 1px solid var(--drive-border); border-bottom: 1px solid var(--drive-border);">
                                    <div class="text-center flex-fill">
                                        <i class="bi bi-tree d-block mb-1" style="font-size: 1.2rem; color: var(--drive-primary);"></i>
                                        <span style="font-size: 0.7rem; color: #6c757d;">Garden</span>
                                    </div>
                                    <div class="text-center flex-fill">
                                        <i class="bi bi-grid-3x3-gap d-block mb-1" style="font-size: 1.2rem; color: var(--drive-primary);"></i>
                                        <span style="font-size: 0.7rem; color: #6c757d;">${totalCount} plots</span>
                                    </div>
                                    <div class="text-center flex-fill">
                                        <i class="bi bi-check2-square d-block mb-1" style="font-size: 1.2rem; color: var(--drive-primary);"></i>
                                        <span style="font-size: 0.7rem; color: #6c757d;">${availableCount} free</span>
                                    </div>
                                    <div class="text-center flex-fill">
                                        <i class="bi bi-rulers d-block mb-1" style="font-size: 1.2rem; color: var(--drive-primary);"></i>
                                        <span style="font-size: 0.7rem; color: #6c757d;">${parseFloat(land.area).toFixed(0)} m²</span>
                                    </div>
                                </div>

                                <!-- Collapsible Description -->
                                <div class="mb-2">
                                    <p class="text-secondary mb-0 land-desc-text" id="desc-text-owner-${land.id}"
                                        style="font-size: 0.82rem; line-height: 1.6; display: none;">
                                        ${land.description || 'No description provided.'}
                                    </p>
                                    <button class="btn btn-link p-0 mt-1 desc-toggle"
                                        style="font-size: 0.75rem; color: var(--drive-primary); font-weight: 500; text-decoration: none;"
                                        onclick="toggleDescOwner(${land.id}, this)">
                                        Description <i class="bi bi-chevron-down" style="font-size: 0.65rem;"></i>
                                    </button>
                                </div>

                                <!-- Landowner Info -->
                                <div class="d-flex align-items-center gap-2 mb-3" style="font-size: 0.75rem; color: #6c757d;">
                                    <div class="rounded-circle bg-success-subtle d-flex align-items-center justify-content-center"
                                        style="width: 24px; height: 24px; min-width:24px;">
                                        <i class="bi bi-person-fill text-success" style="font-size: 0.7rem;"></i>
                                    </div>
                                    <span>${land.landowner || 'John Landowner'}</span>
                                </div>
                            </div>

                            <!-- Card Footer Actions -->
                            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                <div class="d-flex align-items-center gap-2">
                                    <button class="btn btn-sm btn-outline-secondary rounded-pill" onclick="openEditLandModal(${land.id})">
                                        <i class="bi bi-pencil me-1"></i> Edit
                                    </button>
                                    <a href="dashboard.php?id=${land.id}" class="btn btn-sm btn-outline-success rounded-pill" title="View on full screen map">
                                        <i class="bi bi-geo-alt me-1"></i> Map
                                    </a>
                                </div>
                                ${managePlotsBtn}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        // Initialize Leaflet Map preview for each card
        setTimeout(() => {
            if (typeof L === 'undefined') return;

            landsList.forEach(land => {
                const mapId = `map-preview-owner-${land.id}`;
                const el = document.getElementById(mapId);
                if (!el || el._leaflet_id) return;

                const lat = parseFloat(land.latitude) || 14.5995;
                const lng = parseFloat(land.longitude) || 120.9842;
                const area = parseFloat(land.area) || 100;

                const map = L.map(mapId, {
                    zoomControl: false, dragging: true, touchZoom: true,
                    doubleClickZoom: true, scrollWheelZoom: false,
                    boxZoom: false, keyboard: false, attributionControl: false
                }).setView([lat, lng], 15);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);

                const radius = Math.sqrt(area / Math.PI);
                L.circle([lat, lng], {
                    radius: radius,
                    color: '#198754',
                    weight: 2,
                    fillColor: '#198754',
                    fillOpacity: 0.18
                }).addTo(map);

                const pin = L.divIcon({
                    className: '',
                    html: `<div style="background:#198754;width:12px;height:12px;border:2.5px solid #fff;border-radius:50%;margin:-6px 0 0 -6px;box-shadow: 0 2px 5px rgba(0,0,0,0.3);"></div>`,
                    iconSize: [0, 0]
                });
                L.marker([lat, lng], { icon: pin }).addTo(map);

                _leafletMapsOwner[mapId] = map;
            });
        }, 100);
    }

    // Zoom in mini map inside card when clicked
    window.zoomInCardMap = function (landId) {
        const mapInstance = _leafletMapsOwner[`map-preview-owner-${landId}`];
        const land = landsList.find(l => l.id == landId);
        if (mapInstance && land) {
            const lat = parseFloat(land.latitude) || 14.5995;
            const lng = parseFloat(land.longitude) || 120.9842;
            const currentZoom = mapInstance.getZoom();
            const targetZoom = currentZoom >= 18 ? 15 : 18;
            mapInstance.flyTo([lat, lng], targetZoom, { animate: true, duration: 0.8 });
        }
    };

    // Toggle Photo vs Map preview
    window.toggleMediaPreviewOwner = function (landId, btn) {
        const mapEl = document.getElementById(`map-preview-owner-${landId}`);
        const imgEl = document.getElementById(`image-preview-owner-${landId}`);
        if (!mapEl || !imgEl) return;

        const text = btn.querySelector('span');
        const icon = btn.querySelector('i');
        const showingMap = !mapEl.classList.contains('d-none');

        if (showingMap) {
            mapEl.classList.add('d-none');
            imgEl.classList.remove('d-none');
            text.innerText = 'View Map';
            icon.className = 'bi bi-map text-success';
        } else {
            imgEl.classList.add('d-none');
            mapEl.classList.remove('d-none');
            text.innerText = 'View Photo';
            icon.className = 'bi bi-image text-success';

            const mapInstance = _leafletMapsOwner[`map-preview-owner-${landId}`];
            if (mapInstance) setTimeout(() => mapInstance.invalidateSize(), 50);
        }
    };

    // Toggle Collapsible Description
    window.toggleDescOwner = function (landId, btn) {
        const textEl = document.getElementById(`desc-text-owner-${landId}`);
        if (!textEl) return;
        const isHidden = textEl.style.display === 'none';

        if (isHidden) {
            textEl.style.display = 'block';
            btn.innerHTML = `Description <i class="bi bi-chevron-up" style="font-size: 0.65rem;"></i>`;
        } else {
            textEl.style.display = 'none';
            btn.innerHTML = `Description <i class="bi bi-chevron-down" style="font-size: 0.65rem;"></i>`;
        }
    };

    // Open Manage Plots Modal
    window.openManagePlotsModal = function (landId, landTitle) {
        selectedLandId = landId;
        const land = landsList.find(l => l.id == landId);
        const titleStr = landTitle || (land ? land.title : '');

        const modalTitleEl = document.getElementById('modalLandTitle');
        if (modalTitleEl) modalTitleEl.textContent = titleStr;

        const modalLandIdEl = document.getElementById('modalLandId');
        if (modalLandIdEl) modalLandIdEl.value = landId;

        renderPlotsModal(landId);

        if (managePlotsModalEl) {
            managePlotsModalInstance = bootstrap.Modal.getInstance(managePlotsModalEl) || new bootstrap.Modal(managePlotsModalEl);
            managePlotsModalInstance.show();
        }
    };

    // Open Add Plot Modal
    window.openAddPlotModal = function () {
        if (addPlotModalEl) {
            const addModal = new bootstrap.Modal(addPlotModalEl);
            addModal.show();
        }
    };

    // Render partition plots inside the Manage Plots modal
    function renderPlotsModal(landId) {
        if (!modalPlotsContainer) return;
        const landPlots = plotsList.filter(plot => plot.land_id == landId);
        modalPlotsContainer.innerHTML = '';

        if (landPlots.length === 0) {
            modalPlotsContainer.innerHTML = `
                <div class="text-center py-5 border rounded-4 bg-white text-muted">
                    <i class="bi bi-grid-3x3-gap fs-2 d-block mb-2 text-secondary"></i>
                    <span>No plots configured for this land yet. Click "+ Add Plot" to create one.</span>
                </div>
            `;
            return;
        }

        let html = '<div class="d-grid gap-2">';
        landPlots.forEach(plot => {
            let statusBadge = '';
            if (plot.status === 'available') {
                statusBadge = '<span class="badge bg-success rounded-pill px-2.5 py-1" style="font-size: 10px;">Available</span>';
            } else if (plot.status === 'occupied') {
                statusBadge = '<span class="badge bg-primary rounded-pill px-2.5 py-1" style="font-size: 10px;">Occupied</span>';
            } else {
                statusBadge = '<span class="badge bg-secondary rounded-pill px-2.5 py-1" style="font-size: 10px;">Under Maintenance</span>';
            }

            html += `
                <div class="d-flex align-items-center justify-content-between p-3 border rounded-3 bg-light hover:bg-white transition-colors">
                    <div>
                        <div class="fw-bold text-dark mb-1" style="font-size:0.95rem;">${plot.plot_number}</div>
                        <div class="text-muted" style="font-size: 0.78rem;"><i class="bi bi-rulers me-1"></i>Area: ${parseFloat(plot.area).toFixed(1)} m²</div>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        ${statusBadge}
                        <button onclick="deletePlot(${plot.id})" class="btn icon-btn-pill btn-sm text-danger" title="Delete Plot">
                            <i class="bi bi-trash fs-6"></i>
                        </button>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        modalPlotsContainer.innerHTML = html;
    }

    // Open Edit Land Modal
    window.openEditLandModal = function (id) {
        const land = landsList.find(l => l.id === id);
        if (!land) return;

        document.getElementById('edit_land_id').value = land.id;
        document.getElementById('edit_title').value = land.title;
        document.getElementById('edit_address').value = land.address;
        document.getElementById('edit_description').value = land.description;

        const modal = new bootstrap.Modal(editLandModalEl);
        modal.show();
    };

    // Submit Edit Land form
    if (editLandForm) {
        editLandForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            const payload = {
                action: 'update_land',
                land_id: parseInt(document.getElementById('edit_land_id').value),
                title: document.getElementById('edit_title').value,
                address: document.getElementById('edit_address').value,
                description: document.getElementById('edit_description').value
            };

            try {
                const response = await fetch('../api/lands.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const res = await response.json();
                if (res.status === 'success') {
                    const modal = bootstrap.Modal.getInstance(editLandModalEl);
                    if (modal) modal.hide();
                    loadData();
                } else {
                    alert("Error: " + res.message);
                }
            } catch (err) {
                console.error("Update land details failed:", err);
            }
        });
    }

    // Submit Add Plot form
    if (addPlotForm) {
        addPlotForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            const payload = {
                action: 'create_plot',
                land_id: parseInt(document.getElementById('modalLandId').value),
                plot_number: document.getElementById('plot_number').value,
                area: parseFloat(document.getElementById('plot_area').value)
            };

            try {
                const response = await fetch('../api/lands.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const res = await response.json();
                if (res.status === 'success') {
                    const modal = bootstrap.Modal.getInstance(addPlotModalEl);
                    if (modal) modal.hide();
                    addPlotForm.reset();
                    loadData();
                    if (selectedLandId) {
                        renderPlotsModal(selectedLandId);
                    }
                } else {
                    alert("Error: " + res.message);
                }
            } catch (err) {
                console.error("Create plot failed:", err);
            }
        });
    }

    // Delete Plot
    window.deletePlot = async function (plotId) {
        if (!confirm("Are you sure you want to delete this plot?")) return;

        try {
            const response = await fetch('../api/lands.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'delete_plot',
                    plot_id: plotId
                })
            });
            const res = await response.json();
            if (res.status === 'success') {
                loadData();
                if (selectedLandId) {
                    renderPlotsModal(selectedLandId);
                }
            } else {
                alert("Error: " + res.message);
            }
        } catch (err) {
            console.error("Delete plot failed:", err);
        }
    };

    // Initial load
    loadData();
});
