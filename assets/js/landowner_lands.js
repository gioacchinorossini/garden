document.addEventListener("DOMContentLoaded", function () {
    let landsList = [];
    let plotsList = [];
    let selectedLandId = null;

    // DOM Elements
    const landsContainer = document.getElementById('landsContainer');
    const plotsWorkspace = document.getElementById('plotsWorkspace');
    const noPlotsSelected = document.getElementById('noPlotsSelected');
    const activeLandTitle = document.getElementById('activeLandTitle');
    const plotsContainer = document.getElementById('plotsContainer');

    // Modals & Forms
    const editLandModalEl = document.getElementById('editLandModal');
    const editLandForm = document.getElementById('editLandForm');
    const addPlotModalEl = document.getElementById('addPlotModal');
    const addPlotForm = document.getElementById('addPlotForm');

    // Load lands & plots
    async function loadData() {
        try {
            const response = await fetch('../api/lands.php');
            const res = await response.json();
            if (res.status === 'success') {
                landsList = res.data.lands;
                plotsList = res.data.plots;

                renderLands();
                if (selectedLandId) {
                    renderPlots(selectedLandId);
                }
            } else {
                console.error("API error:", res.message);
            }
        } catch (err) {
            console.error("Failed to load lands data:", err);
        }
    }

    // Render properties
    function renderLands() {
        if (landsList.length === 0) {
            landsContainer.innerHTML = `
                <div class="text-center py-5 text-muted bg-white border rounded-4 ">
                    <i class="bi bi-folder-x fs-1 d-block mb-2"></i>
                    <span>No registered properties found.</span>
                </div>
            `;
            return;
        }

        landsContainer.innerHTML = landsList.map(land => {
            let statusBadge = '';
            if (land.status === 'approved') {
                statusBadge = `<span class="badge bg-success rounded-pill" style="font-size: 10px;">Approved</span>`;
            } else if (land.status === 'pending') {
                statusBadge = `<span class="badge bg-warning text-dark rounded-pill" style="font-size: 10px;">Pending Review</span>`;
            } else {
                statusBadge = `<span class="badge bg-danger rounded-pill" style="font-size: 10px;">Rejected</span>`;
            }

            const managePlotsBtn = (land.status === 'approved')
                ? `<button class="btn btn-sm btn-drive-primary" onclick="selectLand(${land.id}, '${land.title.replace(/'/g, "\\'")}')">Manage Plots <i class="bi bi-arrow-right ms-1"></i></button>`
                : `<button class="btn btn-sm btn-drive-secondary" disabled>Plots locked</button>`;

            return `
                <div class="card border rounded-4  mb-3" style="border-color: var(--drive-border) !important;">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-folder-fill text-warning fs-4"></i>
                                <h3 class="fs-6 fw-bold m-0 text-dark">${land.title}</h3>
                            </div>
                            ${statusBadge}
                        </div>

                        <p class="text-muted mb-2" style="font-size: 0.8rem;">${land.description}</p>

                        <div class="p-2 bg-light rounded-3 mb-3 text-secondary" style="font-size: 0.75rem;">
                            <div class="mb-1"><i class="bi bi-geo-alt-fill me-1"></i> ${land.address}</div>
                            <div><i class="bi bi-arrows-angle-expand me-1"></i> Area size: ${parseFloat(land.area).toFixed(1)} m²</div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <button class="btn btn-sm btn-outline-secondary rounded-pill" onclick="openEditLandModal(${land.id})">
                                <i class="bi bi-pencil me-1"></i> Edit details
                            </button>
                            ${managePlotsBtn}
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    // Select land to manage plots
    window.selectLand = function (landId, landTitle) {
        selectedLandId = landId;
        noPlotsSelected.style.setProperty('display', 'none', 'important');
        plotsWorkspace.style.display = 'block';
        activeLandTitle.textContent = landTitle;
        document.getElementById('modalLandId').value = landId;

        renderPlots(landId);
    };

    // Render partition plots for a land
    function renderPlots(landId) {
        const landPlots = plotsList.filter(plot => plot.land_id == landId);
        plotsContainer.innerHTML = '';

        if (landPlots.length === 0) {
            plotsContainer.innerHTML = `
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
                statusBadge = '<span class="badge bg-success rounded-pill px-2" style="font-size: 9px;">Available</span>';
            } else if (plot.status === 'occupied') {
                statusBadge = '<span class="badge bg-primary rounded-pill px-2" style="font-size: 9px;">Occupied</span>';
            } else {
                statusBadge = '<span class="badge bg-secondary rounded-pill px-2" style="font-size: 9px;">Under Maintenance</span>';
            }

            html += `
                <div class="d-flex align-items-center justify-content-between p-3 border rounded-3 bg-white hover:bg-[#f8faff] transition-colors" style="border-color: var(--drive-border) !important;">
                    <div>
                        <div class="fw-semibold text-dark mb-1">${plot.plot_number}</div>
                        <div class="text-muted" style="font-size: 0.75rem;">Area: ${parseFloat(plot.area).toFixed(1)} m²</div>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        ${statusBadge}
                        <button onclick="deletePlot(${plot.id})" class="btn icon-btn-pill btn-sm text-danger" title="Delete Plot">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        plotsContainer.innerHTML = html;
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
