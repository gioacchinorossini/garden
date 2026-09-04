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
                    if (matchLand) {
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

            const managePlotsBtn = `<button class="btn btn-sm btn-drive-primary rounded-pill px-3" onclick="openManagePlotsModal(${land.id}, '${land.title.replace(/'/g, "\\'")}')">Manage Plots <i class="bi bi-arrow-right ms-1"></i></button>`;

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

    // Helper for crop icons in lands JS
    function getPlotCropIconPath(cropName) {
        if (!cropName) return '../assets/crop-icons/tomato/tomato.svg';
        const name = cropName.toLowerCase();
        if (name.includes('tomato')) return '../assets/crop-icons/tomato/tomato.svg';
        if (name.includes('lettuce') || name.includes('leafy')) return '../assets/crop-icons/romaine/romaine.svg';
        if (name.includes('herb') || name.includes('basil')) return '../assets/crop-icons/basil/basil.svg';
        if (name.includes('pepper')) return '../assets/crop-icons/red-bell-pepper/red-bell-pepper.svg';
        if (name.includes('carrot') || name.includes('root')) return '../assets/crop-icons/carrot/carrot.svg';
        if (name.includes('eggplant')) return '../assets/crop-icons/eggplant/eggplant.svg';
        if (name.includes('cucumber')) return '../assets/crop-icons/cucumber/cucumber.svg';
        if (name.includes('spinach')) return '../assets/crop-icons/spinach/spinach.svg';
        if (name.includes('bean') || name.includes('legume')) return '../assets/crop-icons/broad-bean/broad-bean.svg';
        if (name.includes('squash')) return '../assets/crop-icons/yellow-squash/yellow-squash.svg';
        if (name.includes('onion')) return '../assets/crop-icons/red-onion/red-onion.svg';
        if (name.includes('corn')) return '../assets/crop-icons/corn/corn.svg';
        if (name.includes('garlic')) return '../assets/crop-icons/garlic/garlic.svg';
        if (name.includes('potato') || name.includes('tuber')) return '../assets/crop-icons/russet-potato/russet-potato.svg';
        return '../assets/crop-icons/tomato/tomato.svg';
    }

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

        let html = '<div class="d-grid gap-2.5">';
        landPlots.forEach(plot => {
            let statusBadge = '';
            if (plot.status === 'available') {
                statusBadge = '<span class="badge bg-success rounded-pill px-2.5 py-1" style="font-size: 10px;">Available</span>';
            } else if (plot.status === 'occupied') {
                statusBadge = '<span class="badge bg-primary rounded-pill px-2.5 py-1" style="font-size: 10px;">Occupied</span>';
            } else {
                statusBadge = '<span class="badge bg-secondary rounded-pill px-2.5 py-1" style="font-size: 10px;">Under Maintenance</span>';
            }

            const allowedCrops = (plot.crops && plot.crops.length > 0) 
                ? plot.crops 
                : (plot.crop ? [plot.crop] : ['Tomato', 'Lettuce']);

            let cropsBadgesHtml = allowedCrops.map(c => `
                <span class="d-inline-flex align-items-center gap-1.5 px-2 py-0.5 rounded-pill bg-white border text-xs text-dark font-medium shadow-2xs" style="font-size: 11px;">
                    <img src="${getPlotCropIconPath(c)}" alt="${c}" style="width:14px;height:14px;object-fit:contain;">
                    <span>${c}</span>
                </span>
            `).join('');

            html += `
                <div class="d-flex align-items-center justify-content-between p-3 border rounded-3 bg-light hover:bg-white transition-colors">
                    <div>
                        <div class="fw-bold text-dark mb-1 d-flex align-items-center gap-2" style="font-size:0.95rem;">
                            <span>${plot.plot_number}</span>
                            ${statusBadge}
                        </div>
                        <div class="text-muted mb-2" style="font-size: 0.78rem;">
                            <i class="bi bi-rulers me-1"></i>Area: ${parseFloat(plot.area).toFixed(1)} m²
                        </div>
                        <div class="d-flex align-items-center gap-1.5 flex-wrap">
                            <span class="text-muted uppercase text-[10px] font-bold me-1">Permitted Crops:</span>
                            ${cropsBadgesHtml}
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button onclick="openEditPlotCropsModal(${plot.id}, '${plot.plot_number.replace(/'/g, "\\'")}')" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1.5 text-xs d-flex align-items-center gap-1 font-semibold">
                            <i class="bi bi-sprout"></i> Set Permitted Crops
                        </button>
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

    // Permitted Crops Modal Logic for Individual Plot
    const currentPlotSelectedCrops = new Map();
    let currentEditingPlotId = null;

    window.openEditPlotCropsModal = function(plotId, plotNumber) {
        currentEditingPlotId = plotId;
        const plotIdInput = document.getElementById('edit_plot_id');
        if (plotIdInput) plotIdInput.value = plotId;

        const titleEl = document.getElementById('modalPlotTitle');
        if (titleEl) titleEl.textContent = plotNumber;

        const plot = plotsList.find(p => p.id == plotId);
        currentPlotSelectedCrops.clear();

        const allowedCrops = (plot && plot.crops && plot.crops.length > 0) 
            ? plot.crops 
            : (plot && plot.crop ? [plot.crop] : []);

        document.querySelectorAll('.plot-crop-chip').forEach(chip => {
            const val = chip.getAttribute('data-value');
            const label = chip.getAttribute('data-label');
            const icon = chip.getAttribute('data-icon');
            const checkIcon = chip.querySelector('.plot-crop-check-icon');

            const isSelected = allowedCrops.some(c => c.toLowerCase() === val.toLowerCase() || c.toLowerCase() === label.toLowerCase());

            if (isSelected) {
                currentPlotSelectedCrops.set(val, { value: val, label: label, icon: icon });
                chip.classList.add('border-primary', 'bg-blue-50/50');
                chip.style.borderColor = '#198754';
                if (checkIcon) checkIcon.classList.remove('d-none');
            } else {
                chip.classList.remove('border-primary', 'bg-blue-50/50');
                chip.style.borderColor = 'var(--drive-border)';
                if (checkIcon) checkIcon.classList.add('d-none');
            }
        });

        const countEl = document.getElementById('plotSelectedCropCount');
        if (countEl) countEl.textContent = currentPlotSelectedCrops.size;

        const modalEl = document.getElementById('editPlotCropsModal');
        if (modalEl) {
            const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
            modal.show();
        }
    };

    window.togglePlotCropSelection = function(val, label, icon, element) {
        const checkIcon = element.querySelector('.plot-crop-check-icon');
        if (currentPlotSelectedCrops.has(val)) {
            currentPlotSelectedCrops.delete(val);
            element.classList.remove('border-primary', 'bg-blue-50/50');
            element.style.borderColor = 'var(--drive-border)';
            if (checkIcon) checkIcon.classList.add('d-none');
        } else {
            currentPlotSelectedCrops.set(val, { value: val, label: label, icon: icon });
            element.classList.add('border-primary', 'bg-blue-50/50');
            element.style.borderColor = '#198754';
            if (checkIcon) checkIcon.classList.remove('d-none');
        }
        const countEl = document.getElementById('plotSelectedCropCount');
        if (countEl) countEl.textContent = currentPlotSelectedCrops.size;
    };

    window.savePlotCrops = async function() {
        if (!currentEditingPlotId) return;

        const crops = Array.from(currentPlotSelectedCrops.values()).map(c => c.label);
        const primaryCrop = crops[0] || 'General';
        const primaryIcon = (currentPlotSelectedCrops.values().next().value || {}).icon || '';

        try {
            const response = await fetch('../api/lands.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'update_plot_crops',
                    plot_id: currentEditingPlotId,
                    crops: crops,
                    crop: primaryCrop,
                    crop_icon: primaryIcon
                })
            });

            const res = await response.json();
            if (res.status === 'success') {
                const plot = plotsList.find(p => p.id == currentEditingPlotId);
                if (plot) {
                    plot.crops = crops;
                    plot.crop = primaryCrop;
                    plot.crop_icon = primaryIcon;
                }

                const modalEl = document.getElementById('editPlotCropsModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();

                if (selectedLandId) renderPlotsModal(selectedLandId);
            } else {
                alert(res.message || 'Failed to save plot crops.');
            }
        } catch (err) {
            console.error("Error saving plot crops:", err);
        }
    };

    // Edit Land Crops state
    const editSelectedCrops = new Map();
    let editPickerMapInstance = null;
    let editPickerMarker = null;

    window.toggleEditCropSelection = function(val, label, icon, element) {
        const checkIcon = element.querySelector('.edit-crop-check-icon');
        if (editSelectedCrops.has(val)) {
            editSelectedCrops.delete(val);
            element.classList.remove('border-primary', 'bg-blue-50/50');
            element.style.borderColor = 'var(--drive-border)';
            if (checkIcon) checkIcon.classList.add('d-none');
        } else {
            editSelectedCrops.set(val, { value: val, label: label, icon: icon });
            element.classList.add('border-primary', 'bg-blue-50/50');
            element.style.borderColor = '#1a73e8';
            if (checkIcon) checkIcon.classList.remove('d-none');
        }
        renderEditSelectedCropsSummary();
    };

    window.selectAllEditCrops = function(select) {
        document.querySelectorAll('.edit-modal-crop-chip').forEach(chip => {
            const val = chip.getAttribute('data-value');
            const label = chip.getAttribute('data-label');
            const icon = chip.getAttribute('data-icon');
            const checkIcon = chip.querySelector('.edit-crop-check-icon');

            if (select) {
                editSelectedCrops.set(val, { value: val, label: label, icon: icon });
                chip.classList.add('border-primary', 'bg-blue-50/50');
                chip.style.borderColor = '#1a73e8';
                if (checkIcon) checkIcon.classList.remove('d-none');
            } else {
                editSelectedCrops.delete(val);
                chip.classList.remove('border-primary', 'bg-blue-50/50');
                chip.style.borderColor = 'var(--drive-border)';
                if (checkIcon) checkIcon.classList.add('d-none');
            }
        });
        renderEditSelectedCropsSummary();
    };

    window.filterEditCropChips = function() {
        const query = (document.getElementById('editCropSearchInput')?.value || '').toLowerCase().trim();
        document.querySelectorAll('.edit-crop-grid-item').forEach(item => {
            const name = item.getAttribute('data-name');
            if (!query || name.includes(query)) {
                item.classList.remove('d-none');
            } else {
                item.classList.add('d-none');
            }
        });
    };

    function renderEditSelectedCropsSummary() {
        const summaryBox = document.getElementById('editSelectedCropsSummary');
        const hiddenInputs = document.getElementById('editHiddenSeedInputs');
        const countText = document.getElementById('editSelectedCountText');

        if (countText) countText.textContent = editSelectedCrops.size;

        if (!summaryBox) return;

        if (editSelectedCrops.size === 0) {
            summaryBox.innerHTML = `<span class="text-muted text-xs italic" id="editEmptyCropsNotice">No specific crops selected (All crops permitted by default).</span>`;
            if (hiddenInputs) hiddenInputs.innerHTML = '';
            return;
        }

        let summaryHtml = '';
        let inputsHtml = '';

        editSelectedCrops.forEach((crop) => {
            summaryHtml += `
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1.5 border rounded-pill bg-white text-xs fw-semibold text-dark shadow-xs" style="border-color: var(--drive-border) !important;">
                    <img src="${crop.icon}" alt="${crop.label}" style="width:16px;height:16px;object-fit:contain;">
                    <span>${crop.label}</span>
                    <button type="button" class="btn-close text-xs ms-1" style="width: 10px; height: 10px;" onclick="toggleEditCropSelection('${crop.value}', '${crop.label}', '${crop.icon}', document.querySelector('.edit-modal-crop-chip[data-value=\"${crop.value}\"]'))"></button>
                </div>
            `;
            inputsHtml += `<input type="checkbox" name="allowed_seeds[]" value="${crop.value}" checked class="d-none">`;
        });

        summaryBox.innerHTML = summaryHtml;
        if (hiddenInputs) hiddenInputs.innerHTML = inputsHtml;
    }

    // Open Edit Land Modal
    window.openEditLandModal = function (id) {
        const land = landsList.find(l => l.id == id);
        if (!land) return;

        document.getElementById('edit_land_id').value = land.id;
        document.getElementById('edit_title').value = land.title || '';
        document.getElementById('edit_address').value = land.address || '';
        document.getElementById('edit_area').value = land.area || 100;
        
        const lat = parseFloat(land.latitude) || 14.5995;
        const lng = parseFloat(land.longitude) || 120.9842;
        document.getElementById('edit_latitude').value = lat;
        document.getElementById('edit_longitude').value = lng;
        document.getElementById('edit_description').value = land.description || '';

        // Pre-select crops
        editSelectedCrops.clear();
        const existingCrops = Array.isArray(land.crops) ? land.crops : (land.allowed_seeds || []);
        
        document.querySelectorAll('.edit-modal-crop-chip').forEach(chip => {
            const val = chip.getAttribute('data-value');
            const label = chip.getAttribute('data-label');
            const icon = chip.getAttribute('data-icon');
            const checkIcon = chip.querySelector('.edit-crop-check-icon');

            const isMatched = existingCrops.some(c => c.toLowerCase().includes(val.toLowerCase()) || val.toLowerCase().includes(c.toLowerCase()) || c.toLowerCase().includes(label.toLowerCase()));

            if (isMatched) {
                editSelectedCrops.set(val, { value: val, label: label, icon: icon });
                chip.classList.add('border-primary', 'bg-blue-50/50');
                chip.style.borderColor = '#1a73e8';
                if (checkIcon) checkIcon.classList.remove('d-none');
            } else {
                chip.classList.remove('border-primary', 'bg-blue-50/50');
                chip.style.borderColor = 'var(--drive-border)';
                if (checkIcon) checkIcon.classList.add('d-none');
            }
        });
        renderEditSelectedCropsSummary();

        const modal = new bootstrap.Modal(editLandModalEl);
        modal.show();

        // Initialize Map Picker inside modal after visible
        setTimeout(() => {
            if (typeof L === 'undefined') return;

            const mapContainer = document.getElementById('editPickerMap');
            if (!mapContainer) return;

            if (editPickerMapInstance) {
                editPickerMapInstance.remove();
                editPickerMapInstance = null;
            }

            editPickerMapInstance = L.map('editPickerMap').setView([lat, lng], 14);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap contributors'
            }).addTo(editPickerMapInstance);

            editPickerMarker = L.marker([lat, lng]).addTo(editPickerMapInstance);

            editPickerMapInstance.on('click', function (e) {
                const clickLat = e.latlng.lat.toFixed(6);
                const clickLng = e.latlng.lng.toFixed(6);

                document.getElementById('edit_latitude').value = clickLat;
                document.getElementById('edit_longitude').value = clickLng;

                if (editPickerMarker) {
                    editPickerMarker.setLatLng(e.latlng);
                } else {
                    editPickerMarker = L.marker(e.latlng).addTo(editPickerMapInstance);
                }
            });

            editPickerMapInstance.invalidateSize();
        }, 300);
    };

    // Submit Edit Land form
    if (editLandForm) {
        editLandForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            const selectedCropsArr = Array.from(editSelectedCrops.values()).map(c => c.label);

            const payload = {
                action: 'update_land',
                land_id: parseInt(document.getElementById('edit_land_id').value),
                title: document.getElementById('edit_title').value,
                address: document.getElementById('edit_address').value,
                area: parseFloat(document.getElementById('edit_area').value),
                latitude: parseFloat(document.getElementById('edit_latitude').value),
                longitude: parseFloat(document.getElementById('edit_longitude').value),
                description: document.getElementById('edit_description').value,
                crops: selectedCropsArr.length > 0 ? selectedCropsArr : ['General Gardening']
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
