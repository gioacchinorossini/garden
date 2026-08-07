document.addEventListener("DOMContentLoaded", function() {
    // Read seed data from global window variables
    const allLands = window.allLands || [];
    const allPlots = window.allPlots || [];
    
    // Initialize Leaflet Map
    const map = L.map('searchMap').setView([14.5995, 120.9842], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    const markerGroup = L.layerGroup().addTo(map);

    // Dynamic Request Modal trigger handler
    window.openRequestModal = function(landId, landTitle, plotId, plotNumber, plotArea) {
        document.getElementById('modal_land_id').value = landId;
        document.getElementById('modal_plot_id').value = plotId;
        document.getElementById('modal_land_title').innerText = landTitle;
        document.getElementById('modal_plot_number').innerText = plotNumber;
        document.getElementById('modal_plot_area').innerText = plotArea;
        
        const requestModal = new bootstrap.Modal(document.getElementById('requestPlotModal'));
        requestModal.show();
    };

    // Render markers function
    function updateMarkers(lands) {
        markerGroup.clearLayers();
        document.getElementById('resultsCountText').innerText = `${lands.length} matching spaces`;

        if (lands.length === 0) return;

        const bounds = [];
        lands.forEach(function(land) {
            const landPlots = allPlots.filter(p => p.land_id == land.id);
            
            // Build compatible crop tags
            let cropsHtml = '';
            if (land.crops && land.crops.length > 0) {
                cropsHtml = `<div style="margin-top: 4px; display: flex; flex-wrap: wrap; gap: 4px;">` + 
                    land.crops.map(c => `<span style="background-color:#e8f7ec; color:#0f5132; font-size:9px; font-weight:600; padding:2px 6px; border-radius:4px;">${c}</span>`).join('') +
                    `</div>`;
            }

            // Build plots list
            let plotsListHtml = '<div style="margin-top: 6px; display:flex; flex-direction:column; gap:4px; max-height:120px; overflow-y:auto; padding-right:2px;">';
            if (landPlots.length === 0) {
                plotsListHtml += '<span style="font-size:10px; color:#707973;">No plots partitioned.</span>';
            } else {
                landPlots.forEach(p => {
                    if (p.status === 'available') {
                        plotsListHtml += `
                            <div style="display:flex; justify-content:space-between; align-items:center; background:#f4f6f4; border:1px solid #e1e3e1; border-radius:6px; padding:4px 8px; font-size:10px; margin-bottom: 2px;">
                                <span><strong>${p.plot_number}</strong> (${p.area} m²)</span>
                                <button onclick="window.openRequestModal(${land.id}, '${land.title.replace(/'/g, "\\'")}', ${p.id}, '${p.plot_number}', ${p.area})" style="background-color:#198754; color:white; border:none; padding:3.5px 7px; border-radius:4px; font-size:9px; font-weight:bold; cursor:pointer;">Request</button>
                            </div>
                        `;
                    } else {
                        plotsListHtml += `
                            <div style="display:flex; justify-content:space-between; align-items:center; background:#eef1ee; border:1px solid #e1e3e1; border-radius:6px; padding:4px 8px; font-size:10px; color:#707973; margin-bottom: 2px;">
                                <span><strong>${p.plot_number}</strong> (${p.area} m²)</span>
                                <span style="font-weight:500;">Occupied</span>
                            </div>
                        `;
                    }
                });
            }
            plotsListHtml += '</div>';

            // Google Drive style primary green pin
            const color = '#198754';
            const customIcon = L.divIcon({
                className: 'custom-icon',
                html: `<div style="background-color: ${color}; width: 18px; height: 18px; border: 2.5px solid white; border-radius: 50%; box-shadow: 0 1px 4px rgba(0,0,0,0.4)"></div>`,
                iconSize: [18, 18]
            });

            const lat = parseFloat(land.latitude);
            const lng = parseFloat(land.longitude);
            bounds.push([lat, lng]);

            L.marker([lat, lng], { icon: customIcon })
                .addTo(markerGroup)
                .bindPopup(`
                    <div style="font-family: 'Outfit', sans-serif; min-width: 210px; max-width: 250px;">
                        <strong style="font-size:13px; color: #191c1a; display:block; margin-bottom: 2px;">${land.title}</strong>
                        <span style="font-size:10px; color:#707973; display:block; margin-bottom: 6px;"><i class="bi bi-geo-alt-fill"></i> ${land.address}</span>
                        <span style="font-size:10px; display:block; color:#404943; margin-bottom: 6px;"><strong>Total Size:</strong> ${land.area} m²</span>
                        
                        <div style="margin-bottom: 8px;">
                            <span style="font-size: 9px; font-weight:700; color:#707973; text-transform:uppercase; display:block;">Suitable Crops:</span>
                            ${cropsHtml}
                        </div>
                        
                        <div>
                            <span style="font-size: 9px; font-weight:700; color:#707973; text-transform:uppercase; display:block;">Plots:</span>
                            ${plotsListHtml}
                        </div>
                    </div>
                `);
        });

        if (bounds.length > 0) {
            map.fitBounds(bounds, { padding: [50, 50] });
        }
    }

    // Live search and filtering action
    function applyFilters() {
        const query = document.getElementById('searchQueryInput').value.toLowerCase().trim();
        const minArea = parseFloat(document.getElementById('minAreaInput').value);
        const availableOnly = document.getElementById('availableOnlyInput').checked;

        // Collect checked crops
        const selectedCrops = [];
        document.querySelectorAll('.crop-checkbox:checked').forEach(cb => {
            selectedCrops.push(cb.value);
        });

        // Sync inputs with mobile overlay
        const mobileQuery = document.getElementById('mobileSearchQueryInput').value.toLowerCase().trim();
        const mobileAvailableOnly = document.getElementById('mobileAvailableOnlyInput').checked;
        const mobileSelectedCrops = [];
        document.querySelectorAll('.mobile-crop-checkbox:checked').forEach(cb => {
            mobileSelectedCrops.push(cb.value);
        });

        const filtered = allLands.filter(land => {
            // 1. Text keyword search
            const activeQuery = query || mobileQuery;
            if (activeQuery !== '') {
                const titleMatch = land.title.toLowerCase().includes(activeQuery);
                const descMatch = land.description.toLowerCase().includes(activeQuery);
                const addrMatch = land.address.toLowerCase().includes(activeQuery);
                const cropMatch = land.crops.some(c => c.toLowerCase().includes(activeQuery));
                if (!titleMatch && !descMatch && !addrMatch && !cropMatch) return false;
            }

            // 2. Size boundary check
            if (land.area < minArea) return false;

            // 3. Availability check
            if (availableOnly || mobileAvailableOnly) {
                const landPlots = allPlots.filter(p => p.land_id == land.id && p.status === 'available');
                if (landPlots.length === 0) return false;
            }

            // 4. Crop category matches
            const activeCrops = selectedCrops.length > 0 ? selectedCrops : mobileSelectedCrops;
            if (activeCrops.length > 0) {
                const hasCrop = activeCrops.some(c => land.crops.includes(c));
                if (!hasCrop) return false;
            }

            return true;
        });

        updateMarkers(filtered);
    }

    // Hook listeners
    document.getElementById('searchQueryInput').addEventListener('input', applyFilters);
    document.getElementById('minAreaInput').addEventListener('input', function(e) {
        document.getElementById('areaVal').innerText = e.target.value + ' m²';
        applyFilters();
    });
    document.getElementById('availableOnlyInput').addEventListener('change', applyFilters);
    
    document.querySelectorAll('.crop-checkbox').forEach(cb => {
        cb.addEventListener('change', applyFilters);
    });

    // Mobile inputs sync hooks
    document.getElementById('mobileSearchQueryInput').addEventListener('input', function(e) {
        document.getElementById('searchQueryInput').value = e.target.value;
        applyFilters();
    });
    document.getElementById('mobileAvailableOnlyInput').addEventListener('change', function(e) {
        document.getElementById('availableOnlyInput').checked = e.target.checked;
        applyFilters();
    });
    document.querySelectorAll('.mobile-crop-checkbox').forEach((mcb, index) => {
        mcb.addEventListener('change', function(e) {
            const companionCheck = document.querySelectorAll('.crop-checkbox')[index];
            if (companionCheck) {
                companionCheck.checked = e.target.checked;
            }
            applyFilters();
        });
    });

    // Filter resets
    function clearAll() {
        document.getElementById('searchQueryInput').value = '';
        document.getElementById('mobileSearchQueryInput').value = '';
        document.getElementById('minAreaInput').value = 0;
        document.getElementById('areaVal').innerText = '0 m²';
        document.getElementById('availableOnlyInput').checked = false;
        document.getElementById('mobileAvailableOnlyInput').checked = false;
        
        document.querySelectorAll('.crop-checkbox').forEach(cb => cb.checked = false);
        document.querySelectorAll('.mobile-crop-checkbox').forEach(cb => cb.checked = false);
        
        applyFilters();
    }

    document.getElementById('clearFiltersBtn').addEventListener('click', clearAll);
    document.getElementById('mobileClearFiltersBtn').addEventListener('click', clearAll);

    // Form submission via AJAX Fetch API
    const requestForm = document.getElementById('requestPlotForm');
    if (requestForm) {
        requestForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const payload = {
                action: 'submit_request',
                land_id: parseInt(document.getElementById('modal_land_id').value),
                plot_id: parseInt(document.getElementById('modal_plot_id').value),
                duration: document.getElementById('duration').value,
                purpose: document.getElementById('purpose').value
            };

            try {
                const response = await fetch('../api/requests.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const res = await response.json();
                
                if (res.status === 'success') {
                    // Close BS Modal
                    const modalEl = document.getElementById('requestPlotModal');
                    const bsModal = bootstrap.Modal.getInstance(modalEl);
                    if (bsModal) {
                        bsModal.hide();
                    }
                    
                    requestForm.reset();

                    // Display Alert Banner
                    const alertPlaceholder = document.getElementById('alertPlaceholder');
                    if (alertPlaceholder) {
                        alertPlaceholder.innerHTML = `
                            <div class="alert alert-success d-flex align-items-center justify-content-between gap-2 border-0 shadow-lg p-3 m-0" role="alert" style="border-radius: 12px; background-color: #d1e7dd; color: #0f5132;">
                                <span class="flex items-center gap-2"><i class="bi bi-check-circle-fill"></i> ${res.message}</span>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        `;
                        // Auto dismiss after 5 seconds
                        setTimeout(() => {
                            alertPlaceholder.innerHTML = '';
                        }, 5000);
                    }
                } else {
                    alert("Error: " + res.message);
                }
            } catch (err) {
                console.error("Submit lease application failed:", err);
            }
        });
    }

    // Initial load call
    applyFilters();
});
