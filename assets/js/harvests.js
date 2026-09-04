document.addEventListener("DOMContentLoaded", function () {
    let harvestData = [];
    let activeView = 'list'; // 'list' or 'grid'

    // DOM Elements
    const listView = document.getElementById('harvestListView');
    const gridView = document.getElementById('harvestGridView');
    const listBtn = document.getElementById('listViewBtn');
    const gridBtn = document.getElementById('gridViewBtn');
    const tableBody = document.getElementById('harvestTableBody');
    const emptyState = document.getElementById('harvestsEmptyState');
    const contentWrapper = document.getElementById('harvestsContentWrapper');
    const recordForm = document.getElementById('recordHarvestForm');

    // Helper for crop badges/icons using open-crop-icons
    function getCropIconData(cropName) {
        const name = cropName.toLowerCase();
        let svgPath = '../assets/crop-icons/generic-plant/generic-plant.svg';
        let bg = 'bg-emerald-50';
        let border = 'border-emerald-100';

        if (name.includes('tomato')) {
            svgPath = '../assets/crop-icons/tomato/tomato.svg';
            bg = 'bg-red-50';
            border = 'border-red-100';
        } else if (name.includes('lettuce') || name.includes('romaine')) {
            svgPath = '../assets/crop-icons/romaine/romaine.svg';
            bg = 'bg-green-50';
            border = 'border-green-100';
        } else if (name.includes('spinach') || name.includes('greens') || name.includes('cabbage')) {
            svgPath = '../assets/crop-icons/green-cabbage/green-cabbage.svg';
            bg = 'bg-green-50';
            border = 'border-green-100';
        } else if (name.includes('carrot')) {
            svgPath = '../assets/crop-icons/carrot/carrot.svg';
            bg = 'bg-amber-50';
            border = 'border-amber-100';
        } else if (name.includes('potato') || name.includes('tuber')) {
            svgPath = '../assets/crop-icons/russet-potato/russet-potato.svg';
            bg = 'bg-amber-50';
            border = 'border-amber-100';
        } else if (name.includes('onion')) {
            svgPath = '../assets/crop-icons/red-onion/red-onion.svg';
            bg = 'bg-purple-50';
            border = 'border-purple-100';
        } else if (name.includes('basil') || name.includes('mint') || name.includes('herb')) {
            svgPath = '../assets/crop-icons/basil/basil.svg';
            bg = 'bg-emerald-50';
            border = 'border-emerald-100';
        } else if (name.includes('strawberry') || name.includes('berry')) {
            svgPath = '../assets/crop-icons/strawberry/strawberry.svg';
            bg = 'bg-red-50';
            border = 'border-red-100';
        } else if (name.includes('corn')) {
            svgPath = '../assets/crop-icons/corn/corn.svg';
            bg = 'bg-amber-50';
            border = 'border-amber-100';
        } else if (name.includes('cucumber')) {
            svgPath = '../assets/crop-icons/cucumber/cucumber.svg';
            bg = 'bg-green-50';
            border = 'border-green-100';
        }

        return { svgPath, bg, border };
    }

    // Format dates client-side
    function formatDate(dateStr) {
        const options = { year: 'numeric', month: 'short', day: 'numeric' };
        const date = new Date(dateStr);
        return date.toLocaleDateString('en-US', options);
    }

    // Toggle view states
    window.switchView = function (view) {
        activeView = view;
        if (activeView === 'grid') {
            listView.classList.add('hidden');
            gridView.classList.remove('hidden');

            gridBtn.classList.add('bg-white', 'text-drive-primary', '');
            gridBtn.classList.remove('text-drive-text-sub', 'hover:bg-drive-surface-hover');

            listBtn.classList.remove('bg-white', 'text-drive-primary', '');
            listBtn.classList.add('text-drive-text-sub', 'hover:bg-drive-surface-hover');
        } else {
            gridView.classList.add('hidden');
            listView.classList.remove('hidden');

            listBtn.classList.add('bg-white', 'text-drive-primary', '');
            listBtn.classList.remove('text-drive-text-sub', 'hover:bg-drive-surface-hover');

            gridBtn.classList.remove('bg-white', 'text-drive-primary', '');
            gridBtn.classList.add('text-drive-text-sub', 'hover:bg-drive-surface-hover');
        }
    };

    // Load yields from API
    async function loadHarvests() {
        try {
            const response = await fetch('../api/harvests.php');
            const res = await response.json();

            if (res.status === 'success') {
                harvestData = res.data.harvests;
                renderUI();
            } else {
                console.error("API error:", res.message);
            }
        } catch (err) {
            console.error("Failed to load harvests:", err);
        }
    }

    // Render logic
    function renderUI() {
        if (harvestData.length === 0) {
            emptyState.classList.remove('hidden');
            contentWrapper.classList.add('hidden');
            return;
        }

        emptyState.classList.add('hidden');
        contentWrapper.classList.remove('hidden');

        // Render List Table View
        tableBody.innerHTML = harvestData.map(h => {
            const icon = getCropIconData(h.crop_name);
            const notesText = h.notes ? h.notes : 'No notes recorded';
            return `
                <div class="flex items-center justify-between px-6 py-3.5 hover:bg-drive-canvas/50 text-xs text-drive-text-main transition-colors">
                    <div class="w-1/4 font-semibold flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center border ${icon.bg} ${icon.border}">
                            <img src="${icon.svgPath}" class="w-5 h-5 object-contain" alt="${h.crop_name}">
                        </div>
                        <span>${h.crop_name}</span>
                    </div>
                    <div class="w-1/6">
                        <span class="bg-drive-canvas text-drive-text-sub border border-drive-border text-[10px] font-semibold px-2.5 py-0.5 rounded-full">${h.plot_num}</span>
                    </div>
                    <div class="w-1/3 text-drive-text-sub truncate pr-4" title="${notesText}">${notesText}</div>
                    <div class="w-1/6 text-drive-text-muted">${formatDate(h.harvest_date)}</div>
                    <div class="w-1/12 text-right text-drive-primary font-bold text-sm">
                        ${parseFloat(h.quantity).toFixed(1)} <span class="text-[10px] font-semibold text-drive-text-sub">${h.unit}</span>
                    </div>
                </div>
            `;
        }).join('');

        // Render Grid View
        gridView.innerHTML = harvestData.map(h => {
            const icon = getCropIconData(h.crop_name);
            const notesText = h.notes ? h.notes : 'No notes or crop health logs recorded.';
            return `
                <div class="bg-white border border-drive-border rounded-2xl p-5 hover:shadow-md transition-all flex flex-col gap-4">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-2xl flex items-center justify-center border ${icon.bg} ${icon.border}">
                                <img src="${icon.svgPath}" class="w-8 h-8 object-contain" alt="${h.crop_name}">
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-drive-text-main">${h.crop_name}</h3>
                                <span class="text-[10px] text-drive-text-muted">${formatDate(h.harvest_date)}</span>
                            </div>
                        </div>
                        <span class="bg-drive-canvas text-drive-text-sub border border-drive-border text-[9px] font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-full">
                            ${h.plot_num}
                        </span>
                    </div>
                    <div class="p-3 bg-drive-canvas border border-drive-border rounded-xl text-xs text-drive-text-sub min-h-[64px] flex flex-col justify-center">
                        <span class="text-[9px] font-bold text-drive-text-muted uppercase tracking-wide block mb-1">OBSERVATIONS:</span>
                        <p class="mb-0 italic leading-relaxed">"${notesText}"</p>
                    </div>
                    <div class="flex items-center justify-between pt-2 border-t border-drive-border text-xs text-drive-text-sub">
                        <span>Logged Yield</span>
                        <span class="text-drive-primary font-bold text-lg">
                            ${parseFloat(h.quantity).toFixed(1)} <span class="text-xs text-drive-text-sub font-semibold">${h.unit}</span>
                        </span>
                    </div>
                </div>
            `;
        }).join('');
    }

    // Submit form handler via Fetch POST API
    if (recordForm) {
        recordForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            const payload = {
                action: 'record_harvest',
                plot_num: document.getElementById('plot_num').value,
                crop_name: document.getElementById('crop_name').value,
                quantity: parseFloat(document.getElementById('quantity').value),
                unit: document.getElementById('unit').value,
                harvest_date: document.getElementById('harvest_date').value,
                notes: document.getElementById('notes').value
            };

            try {
                const response = await fetch('../api/harvests.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const res = await response.json();

                if (res.status === 'success') {
                    // Close BS Modal
                    const modalEl = document.getElementById('recordHarvestModal');
                    const bsModal = bootstrap.Modal.getInstance(modalEl);
                    if (bsModal) {
                        bsModal.hide();
                    }

                    // Reset Form
                    recordForm.reset();

                    // Display Alert
                    const alertContainer = document.getElementById('alertPlaceholder');
                    if (alertContainer) {
                        alertContainer.innerHTML = `
                            <div class="alert alert-success d-flex align-items-center justify-content-between gap-2 border-0  p-3.5 mb-6" role="alert" style="border-radius: 12px; background-color: #d1e7dd; color: #0f5132;">
                                <span class="flex items-center gap-2"><i class="bi bi-check-circle-fill"></i> ${res.message}</span>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        `;
                    }

                    // Reload
                    loadHarvests();
                } else {
                    alert("Error: " + res.message);
                }
            } catch (err) {
                console.error("Submission failed:", err);
            }
        });
    }

    // Load initial
    loadHarvests();
});
