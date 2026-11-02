document.addEventListener("DOMContentLoaded", function () {
    let harvestData = [];
    let summaryData = null;

    // Default view:
    // Mobile/tablet (< 768px) = Grid
    // Desktop (>= 768px) = List
    let activeView = window.innerWidth < 768 ? 'grid' : 'list';

    // DOM Elements
    const listView = document.getElementById('harvestListView');
    const gridView = document.getElementById('harvestGridView');
    const listBtn = document.getElementById('listViewBtn');
    const gridBtn = document.getElementById('gridViewBtn');
    const tableBody = document.getElementById('harvestTableBody');
    const emptyState = document.getElementById('harvestsEmptyState');
    const contentWrapper = document.getElementById('harvestsContentWrapper');
    const recordForm = document.getElementById('recordHarvestForm');

    // Summary Elements
    const summaryYieldEl = document.getElementById('summaryYield');
    const summaryIncomeEl = document.getElementById('summaryIncome');
    const summaryRentEl = document.getElementById('summaryRent');
    const summaryProfitEl = document.getElementById('summaryProfit');

    // Modal Calculation Elements
    const plotSelect = document.getElementById('plot_num');
    const incomeInput = document.getElementById('declared_income');
    const rentInput = document.getElementById('rental_expense');
    const otherInput = document.getElementById('other_expenses');
    const calcEquationEl = document.getElementById('calcEquationPreview');
    const calcProfitEl = document.getElementById('calcNetProfitPreview');

    // Currency Formatter
    function formatPHP(amount) {
        const val = parseFloat(amount) || 0;
        return '₱' + val.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Helper for crop badges/icons using open-crop-icons
    function getCropIconData(cropName) {
        const name = (cropName || '').toLowerCase();

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
        if (!dateStr) return '';
        const d = new Date(dateStr.replace(' ', 'T'));
        if (isNaN(d.getTime())) return dateStr;
        return d.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
    }

    // Switch between list and grid views
    window.switchView = function (view) {
        activeView = view;

        if (view === 'grid') {
            listView.classList.add('hidden');
            gridView.classList.remove('hidden');

            gridBtn.classList.add('bg-white', 'text-drive-primary');
            gridBtn.classList.remove('text-drive-text-sub', 'hover:bg-drive-surface-hover');

            listBtn.classList.remove('bg-white', 'text-drive-primary');
            listBtn.classList.add('text-drive-text-sub', 'hover:bg-drive-surface-hover');
        } else {
            gridView.classList.add('hidden');
            listView.classList.remove('hidden');

            listBtn.classList.add('bg-white', 'text-drive-primary');
            listBtn.classList.remove('text-drive-text-sub', 'hover:bg-drive-surface-hover');

            gridBtn.classList.remove('bg-white', 'text-drive-primary');
            gridBtn.classList.add('text-drive-text-sub', 'hover:bg-drive-surface-hover');
        }
    };

    // Live Modal Net Profit Calculation: (Income - Expenses = Profit)
    function updateModalProfitPreview() {
        if (!incomeInput || !rentInput || !calcProfitEl) return;

        const income = parseFloat(incomeInput.value) || 0;
        const rent = parseFloat(rentInput.value) || 0;
        const other = parseFloat(otherInput ? otherInput.value : 0) || 0;
        const net = income - rent - other;

        if (calcEquationEl) {
            calcEquationEl.textContent = `${formatPHP(income)} − ${formatPHP(rent)}${other > 0 ? ` − ${formatPHP(other)}` : ''} =`;
        }

        calcProfitEl.textContent = (net >= 0 ? '+' : '') + formatPHP(net);
        if (net >= 0) {
            calcProfitEl.className = 'fs-5 fw-bold text-success';
        } else {
            calcProfitEl.className = 'fs-5 fw-bold text-danger';
        }
    }

    if (plotSelect) {
        plotSelect.addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            if (opt && opt.dataset.rent && rentInput) {
                rentInput.value = opt.dataset.rent;
            }
            updateModalProfitPreview();
        });
    }

    if (incomeInput) incomeInput.addEventListener('input', updateModalProfitPreview);
    if (rentInput) rentInput.addEventListener('input', updateModalProfitPreview);
    if (otherInput) otherInput.addEventListener('input', updateModalProfitPreview);

    // Initial calculation run
    updateModalProfitPreview();

    // Load yields from API
    async function loadHarvests() {
        try {
            const response = await fetch('../api/harvests.php');
            const res = await response.json();

            if (res.status === 'success') {
                harvestData = res.data.harvests || [];
                summaryData = res.data.summary || null;

                renderSummary();
                renderUI();
            } else {
                console.error("API error:", res.message);
            }
        } catch (err) {
            console.error("Failed to load harvests:", err);
        }
    }

    // Render P&L Summary Cards
    function renderSummary() {
        if (!summaryData) return;

        if (summaryYieldEl) {
            summaryYieldEl.textContent = (parseFloat(summaryData.total_yield_kg) || 0).toLocaleString('en-US', { minimumFractionDigits: 1, maximumFractionDigits: 1 });
        }
        if (summaryIncomeEl) {
            summaryIncomeEl.textContent = formatPHP(summaryData.total_declared_income);
        }
        if (summaryRentEl) {
            summaryRentEl.textContent = '−' + formatPHP(summaryData.total_rental_expense);
        }
        if (summaryProfitEl) {
            const net = parseFloat(summaryData.total_net_profit) || 0;
            summaryProfitEl.textContent = (net >= 0 ? '+' : '') + formatPHP(net);
            summaryProfitEl.className = net >= 0 ? 'text-lg font-black text-emerald-800' : 'text-lg font-black text-rose-700';
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

        // -----------------------------------------
        // Render List Table View
        // -----------------------------------------
        tableBody.innerHTML = harvestData.map(h => {
            const icon = getCropIconData(h.crop_name);
            const notesText = h.notes ? h.notes : 'Standard harvest declaration';
            const income = parseFloat(h.declared_income) || 0;
            const rent = parseFloat(h.rental_expense) || 0;
            const net = parseFloat(h.net_profit) || 0;

            const netClass = net >= 0 
                ? 'bg-emerald-50 text-emerald-800 border-emerald-200' 
                : 'bg-rose-50 text-rose-800 border-rose-200';

            return `
                <div class="flex items-center justify-between px-6 py-3.5 hover:bg-drive-canvas/50 text-xs text-drive-text-main transition-colors">
                    <!-- 1. Crop & Date -->
                    <div style="width: 25%;" class="font-semibold flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center border ${icon.bg} ${icon.border} flex-shrink-0">
                            <img src="${icon.svgPath}" class="w-5 h-5 object-contain" alt="${h.crop_name}">
                        </div>
                        <div class="min-w-0">
                            <div class="truncate text-drive-text-main">${h.crop_name}</div>
                            <div class="text-[10px] text-drive-text-muted font-normal">${formatDate(h.harvest_date)}</div>
                        </div>
                    </div>

                    <!-- 2. Plot & Standard Area -->
                    <div style="width: 20%;">
                        <span class="bg-drive-canvas text-drive-text-main border border-drive-border text-[10px] font-bold px-2.5 py-0.5 rounded-full inline-block">
                            ${h.plot_num}
                        </span>
                        <div class="text-[10px] text-drive-text-muted mt-0.5">50 m² • Rent: ${formatPHP(rent)}</div>
                    </div>

                    <!-- 3. Yield Quantity -->
                    <div style="width: 15%;" class="text-end font-bold text-dark">
                        ${parseFloat(h.quantity).toFixed(1)} <span class="text-[10px] text-drive-text-sub font-normal">kg</span>
                    </div>

                    <!-- 4. Declared Sales / Gross Income -->
                    <div style="width: 20%;" class="text-end">
                        <span class="font-bold text-blue-700">${formatPHP(income)}</span>
                        <div class="text-[10px] text-muted">Crop Sales</div>
                    </div>

                    <!-- 5. Net Profit (Income - Rent) -->
                    <div style="width: 20%;" class="text-end">
                        <span class="badge border rounded-pill px-2.5 py-1 text-xs font-bold ${netClass}" title="${formatPHP(income)} (Sales) − ${formatPHP(rent)} (Rent) = ${formatPHP(net)} Net">
                            ${net >= 0 ? '+' : ''}${formatPHP(net)}
                        </span>
                        <div class="text-[9px] text-muted mt-0.5">Net Gain</div>
                    </div>
                </div>
            `;
        }).join('');

        // -----------------------------------------
        // Render Grid View
        // -----------------------------------------
        gridView.innerHTML = harvestData.map(h => {
            const icon = getCropIconData(h.crop_name);
            const notesText = h.notes ? h.notes : 'No extra observations recorded.';
            const income = parseFloat(h.declared_income) || 0;
            const rent = parseFloat(h.rental_expense) || 0;
            const other = parseFloat(h.other_expenses) || 0;
            const net = parseFloat(h.net_profit) || 0;

            const netBadgeBg = net >= 0 ? 'background:#EBF3E8; border-color:#BDDAB8; color:#2D5A27;' : 'background:#FDE8E8; border-color:#F8B4B4; color:#9B1C1C;';

            return `
                <div class="bg-white border border-drive-border rounded-2xl p-5 hover:shadow-md transition-all flex flex-col gap-4">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-2xl flex items-center justify-center border ${icon.bg} ${icon.border} flex-shrink-0">
                                <img src="${icon.svgPath}" class="w-8 h-8 object-contain" alt="${h.crop_name}">
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-drive-text-main m-0">${h.crop_name}</h3>
                                <span class="text-[10px] text-drive-text-muted">${formatDate(h.harvest_date)}</span>
                            </div>
                        </div>

                        <span class="bg-drive-canvas text-drive-text-sub border border-drive-border text-[9px] font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-full flex-shrink-0">
                            ${h.plot_num}
                        </span>
                    </div>

                    <!-- Financial Calculation Breakdown Card (Income - Expenses = Profit) -->
                    <div class="p-3 rounded-xl border" style="background:#FBF9F5; border-color:#EFE6DC;">
                        <div class="flex items-center justify-between text-xs mb-1">
                            <span class="text-[10px] text-muted font-bold uppercase">Declared Sales</span>
                            <span class="font-bold text-blue-700">${formatPHP(income)}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs mb-1">
                            <span class="text-[10px] text-muted font-bold uppercase">Land Rental Expense</span>
                            <span class="font-bold text-rose-600">−${formatPHP(rent)}</span>
                        </div>
                        ${other > 0 ? `
                        <div class="flex items-center justify-between text-xs mb-1">
                            <span class="text-[10px] text-muted font-bold uppercase">Other Expenses</span>
                            <span class="font-bold text-gray-600">−${formatPHP(other)}</span>
                        </div>` : ''}
                        <div class="pt-2 border-t border-dashed border-gray-300 flex items-center justify-between">
                            <span class="text-[10px] font-extrabold uppercase text-dark">Net Profit:</span>
                            <span class="text-xs font-black px-2 py-0.5 rounded-full border" style="${netBadgeBg}">
                                ${net >= 0 ? '+' : ''}${formatPHP(net)}
                            </span>
                        </div>
                    </div>

                    <div class="p-2.5 bg-drive-canvas border border-drive-border rounded-xl text-xs text-drive-text-sub flex flex-col justify-center">
                        <span class="text-[9px] font-bold text-drive-text-muted uppercase tracking-wide block mb-0.5">NOTES:</span>
                        <p class="mb-0 italic leading-relaxed text-[11px]">"${notesText}"</p>
                    </div>

                    <div class="flex items-center justify-between pt-2 border-t border-drive-border text-xs text-drive-text-sub">
                        <span class="font-medium">Total Yield Logged</span>
                        <span class="text-drive-primary font-bold text-base">
                            ${parseFloat(h.quantity).toFixed(1)} <span class="text-xs text-drive-text-sub font-semibold">kg</span>
                        </span>
                    </div>
                </div>
            `;
        }).join('');
    }

    // -----------------------------------------
    // Submit form handler via Fetch POST API
    // -----------------------------------------
    if (recordForm) {
        recordForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            const income = parseFloat(document.getElementById('declared_income').value) || 0;
            const rent = parseFloat(document.getElementById('rental_expense').value) || 0;
            const other = parseFloat(document.getElementById('other_expenses').value) || 0;

            const payload = {
                action: 'record_harvest',
                plot_num: document.getElementById('plot_num').value,
                crop_name: document.getElementById('crop_name').value,
                quantity: parseFloat(document.getElementById('quantity').value) || 0,
                unit: 'kg',
                harvest_date: document.getElementById('harvest_date').value,
                declared_income: income,
                rental_expense: rent,
                other_expenses: other,
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
                    // Close Bootstrap Modal
                    const modalEl = document.getElementById('recordHarvestModal');
                    const bsModal = bootstrap.Modal.getInstance(modalEl);
                    if (bsModal) bsModal.hide();

                    // Reset Form
                    recordForm.reset();
                    if (incomeInput) incomeInput.value = '200000';
                    if (rentInput) rentInput.value = '110000';
                    if (otherInput) otherInput.value = '0';
                    updateModalProfitPreview();

                    // Display Alert
                    const alertContainer = document.getElementById('alertPlaceholder');
                    if (alertContainer) {
                        alertContainer.innerHTML = `
                            <div class="alert alert-success d-flex align-items-center justify-content-between gap-2 border-0 p-3.5 mb-6"
                                role="alert" style="border-radius: 16px; background-color: #EBF3E8; color: #2D5A27; border: 1.5px solid #BDDAB8;">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-check-circle-fill fs-5 text-success"></i>
                                    <div>
                                        <strong class="d-block text-xs">Harvest and Net Profit Recorded!</strong>
                                        <span class="text-[11px] text-muted">Income: ${formatPHP(income)} − Rent: ${formatPHP(rent)} = Net Profit: ${formatPHP(income - rent - other)}</span>
                                    </div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        `;
                    }

                    // Reload table
                    await loadHarvests();
                } else {
                    alert("Error: " + res.message);
                }
            } catch (err) {
                console.error("Submission failed:", err);
                alert("An error occurred while submitting the harvest record.");
            }
        });
    }

    // Set initial view button styles
    switchView(activeView);

    // Initial load
    loadHarvests();
});