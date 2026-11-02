<?php
$base_path = '../';
$page_title = "Record Harvests";
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
?>

<main class="workspace-surface flex-1 flex flex-col overflow-hidden h-full">
    <!-- Toolbar/Title Bar -->
    <div class="h-14 px-6 flex items-center justify-between flex-shrink-0 bg-white">
        <div>
            <h1 class="text-sm font-bold text-drive-text-main flex items-center gap-2">
                <i data-lucide="shopping-bag" style="color: #1a73e8; width: 18px; height: 18px;"></i> Harvest Log
            </h1>
        </div>
        <div class="flex items-center gap-3">
            <!-- Table vs Grid Toggler (hidden on mobile) -->
            <div
                class="d-none d-sm-flex items-center gap-1 border border-drive-border rounded-full p-0.5 bg-drive-canvas">
                <button id="listViewBtn" onclick="switchView('list')"
                    class="px-3 py-1 rounded-full text-xs font-bold flex items-center gap-1.5 transition-all bg-white text-drive-primary ">
                    <i data-lucide="list" style="width: 14px; height: 14px;"></i> List
                </button>
                <button id="gridViewBtn" onclick="switchView('grid')"
                    class="px-3 py-1 rounded-full text-xs font-semibold flex items-center gap-1.5 transition-all text-drive-text-sub hover:bg-drive-surface-hover">
                    <i data-lucide="layout-grid" style="width: 14px; height: 14px;"></i> Grid
                </button>
            </div>

            <button
                class="bg-drive-primary hover:bg-drive-primary-hover text-white font-semibold py-1.5 px-4 rounded-full text-xs transition-colors flex items-center gap-1.5 "
                data-bs-toggle="modal" data-bs-target="#recordHarvestModal">
                <i data-lucide="plus" style="width: 14px; height: 14px;"></i> Record Harvest
            </button>
        </div>
    </div>

    <!-- Workspace Scrollable Area -->
    <div class="flex-1 p-6 overflow-y-auto bg-white">

        <!-- Financial Calculation / P&L Summary Banner (Cats & Soup Aesthetic) -->
        <div class="mb-6 p-4 rounded-3xl border border-drive-border" style="background: linear-gradient(135deg, #FFFDF9 0%, #F5EFE6 100%); box-shadow: 0 4px 16px rgba(82,62,49,0.06);">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-3 pb-3 border-b border-drive-border">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-2xl flex items-center justify-center text-white" style="background: #2D5A27;">
                        <i class="bi bi-calculator-fill" style="font-size: 1.15rem;"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-drive-text-main m-0">Land Rental &amp; Profit Calculator</h2>
                        <span class="text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                            Formula: Declared Income − Land Rental Expenses = Net Profit
                        </span>
                    </div>
                </div>
                <div class="text-xs text-drive-text-sub font-medium">
                    Standard Plot Benchmark: <strong class="text-drive-text-main">50 m² @ ₱110,000 / year</strong>
                </div>
            </div>

            <!-- Metric Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <!-- 1. Total Harvested Yield -->
                <div class="bg-white p-3 rounded-2xl border border-drive-border shadow-xs">
                    <span class="text-[10px] font-bold text-drive-text-muted uppercase tracking-wider block mb-1">TOTAL YIELD</span>
                    <div class="flex items-baseline gap-1">
                        <span class="text-lg font-black text-emerald-800" id="summaryYield">0.0</span>
                        <span class="text-xs font-bold text-drive-text-sub">kg</span>
                    </div>
                </div>

                <!-- 2. Declared Gross Sales -->
                <div class="bg-white p-3 rounded-2xl border border-drive-border shadow-xs">
                    <span class="text-[10px] font-bold text-drive-text-muted uppercase tracking-wider block mb-1">DECLARED INCOME</span>
                    <div class="text-lg font-black text-blue-700" id="summaryIncome">₱0.00</div>
                    <span class="text-[10px] text-drive-text-muted">Crop sales revenue</span>
                </div>

                <!-- 3. Land Rental Expenses -->
                <div class="bg-white p-3 rounded-2xl border border-drive-border shadow-xs">
                    <span class="text-[10px] font-bold text-drive-text-muted uppercase tracking-wider block mb-1">LAND RENT EXPENSE</span>
                    <div class="text-lg font-black text-rose-600" id="summaryRent">−₱0.00</div>
                    <span class="text-[10px] text-drive-text-muted">Plot lease fees</span>
                </div>

                <!-- 4. Net Profit / Income -->
                <div class="p-3 rounded-2xl border border-emerald-300 shadow-xs" style="background: #EBF3E8;">
                    <span class="text-[10px] font-bold text-emerald-900 uppercase tracking-wider block mb-1">NET PROFIT / INCOME</span>
                    <div class="text-lg font-black text-emerald-800" id="summaryProfit">+₱0.00</div>
                    <span class="text-[10px] font-bold text-emerald-700">Retained gardener profit</span>
                </div>
            </div>
        </div>

        <!-- Alert Placement -->
        <div id="alertPlaceholder"></div>

        <!-- Empty State Container -->
        <div id="harvestsEmptyState"
            class="hidden text-center py-16 text-drive-text-muted border border-dashed border-drive-border rounded-2xl">
            <img src="../assets/crop-icons/generic-plant/generic-plant.svg" class="w-12 h-12 mx-auto mb-3" alt="Crops">
            <p class="text-sm font-semibold text-drive-text-main mb-1">No harvest records logged yet</p>
            <button
                class="bg-drive-primary hover:bg-drive-primary-hover text-white font-semibold py-2 px-5 rounded-full text-xs"
                data-bs-toggle="modal" data-bs-target="#recordHarvestModal">
                Log Harvest
            </button>
        </div>

        <!-- Content Wrapper (Contains List and Grid) -->
        <div id="harvestsContentWrapper" class="hidden flex flex-col gap-6">
            <!-- 1. List View Container -->
            <div id="harvestListView" class="border border-drive-border rounded-2xl bg-white overflow-hidden shadow-xs">
                <!-- Header Row -->
                <div
                    class="flex items-center justify-between px-6 py-3 bg-drive-canvas border-b border-drive-border text-[10px] font-bold text-drive-text-sub uppercase tracking-wider">
                    <div style="width: 25%;">Crop &amp; Date</div>
                    <div style="width: 20%;">Plot &amp; Standard</div>
                    <div style="width: 15%;" class="text-end">Yield</div>
                    <div style="width: 20%;" class="text-end">Declared Sales</div>
                    <div style="width: 20%;" class="text-end">Net Profit</div>
                </div>

                <!-- Harvest Rows Placeholder -->
                <div id="harvestTableBody" class="divide-y divide-drive-border"></div>
            </div>

            <!-- 2. Grid View Container -->
            <div id="harvestGridView" class="hidden grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"></div>
        </div>
    </div>
</main>

<!-- Record Harvest Modal with Financial Profit Calculator -->
<div class="modal fade" id="recordHarvestModal" tabindex="-1" aria-hidden="true" style="z-index: 9999;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content drive-modal-content border-0 shadow-xl" style="border-radius: 24px;">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <div>
                    <h5 class="modal-title fw-bold text-dark m-0 d-flex align-items-center gap-2">
                        <i class="bi bi-cash-coin text-success"></i>
                        <span>Log Harvest &amp; Declare Crop Income</span>
                    </h5>
                    <p class="text-muted small mb-0 mt-0.5">Calculate net profit after deducting land lease rent</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-start px-4 pt-3 pb-4">
                <form id="recordHarvestForm">
                    <!-- Plot Selector -->
                    <div class="mb-3">
                        <label for="plot_num" class="block text-[10px] font-bold text-drive-text-sub uppercase tracking-wider mb-1.5">
                            SOURCE PLOT (STANDARD 50 SQ. M.)
                        </label>
                        <select
                            class="w-full bg-white border border-drive-border rounded-xl px-3.5 py-2 text-sm focus:outline-none focus:border-drive-primary"
                            id="plot_num" name="plot_num" required>
                            <option value="Plot B-1" data-rent="110000">Downtown Rooftop Garden — Plot B-1 (50 m² • ₱110,000 / yr)</option>
                            <option value="Plot B-2" data-rent="110000">Downtown Rooftop Garden — Plot B-2 (50 m² • ₱110,000 / yr)</option>
                            <option value="Plot E-1" data-rent="110000">Eastside Clay Meadows — Plot E-1 (50 m² • ₱110,000 / yr)</option>
                            <option value="Plot R-1" data-rent="110000">Riverdale Acres — Plot R-1 (50 m² • ₱110,000 / yr)</option>
                        </select>
                    </div>

                    <!-- Crop Name & Yield Quantity -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
                        <div class="md:col-span-2">
                            <label for="crop_name" class="block text-[10px] font-bold text-drive-text-sub uppercase tracking-wider mb-1.5">
                                CROP HARVESTED
                            </label>
                            <input type="text"
                                class="w-full bg-white border border-drive-border rounded-xl px-3.5 py-2 text-sm focus:outline-none focus:border-drive-primary"
                                id="crop_name" name="crop_name" required placeholder="e.g. Organic Tomatoes">
                        </div>
                        <div>
                            <label for="quantity" class="block text-[10px] font-bold text-drive-text-sub uppercase tracking-wider mb-1.5">
                                HARVEST YIELD (KG)
                            </label>
                            <div class="flex items-center">
                                <input type="number" step="0.1" min="0.1"
                                    class="w-full bg-white border border-drive-border rounded-l-xl px-3 py-2 text-sm focus:outline-none focus:border-drive-primary"
                                    id="quantity" name="quantity" required placeholder="450">
                                <span class="bg-drive-canvas border border-l-0 border-drive-border rounded-r-xl px-3 py-2 text-xs font-bold text-drive-text-sub">kg</span>
                            </div>
                            <input type="hidden" id="unit" name="unit" value="kg">
                        </div>
                    </div>

                    <!-- Financial Calculation Section (Income - Expenses = Profit) -->
                    <div class="p-3.5 rounded-2xl border mb-3" style="background:#F7F4EF; border-color:#DFCFC2;">
                        <div class="text-[11px] font-bold text-dark uppercase tracking-wider mb-2 d-flex align-items-center gap-1.5">
                            <i class="bi bi-pie-chart-fill text-success"></i>
                            <span>Financial Declaration: Income − Expenses = Profit</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <!-- Declared Crop Income -->
                            <div>
                                <label for="declared_income" class="block text-[10px] font-bold text-emerald-800 uppercase tracking-wider mb-1">
                                    DECLARED CROP SALES (₱)
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2 text-xs font-bold text-gray-500">₱</span>
                                    <input type="number" step="100" min="0"
                                        class="w-full bg-white border border-drive-border rounded-xl ps-7 pe-3 py-2 text-sm font-semibold text-dark focus:outline-none focus:border-drive-primary"
                                        id="declared_income" name="declared_income" required placeholder="200000" value="200000">
                                </div>
                                <span class="text-[10px] text-muted">Gardener declared sales</span>
                            </div>

                            <!-- Land Rental Expense -->
                            <div>
                                <label for="rental_expense" class="block text-[10px] font-bold text-rose-800 uppercase tracking-wider mb-1">
                                    LAND RENT EXPENSE (₱)
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2 text-xs font-bold text-gray-500">₱</span>
                                    <input type="number" step="100" min="0"
                                        class="w-full bg-white border border-drive-border rounded-xl ps-7 pe-3 py-2 text-sm font-semibold text-dark focus:outline-none focus:border-drive-primary"
                                        id="rental_expense" name="rental_expense" required placeholder="110000" value="110000">
                                </div>
                                <span class="text-[10px] text-muted">Standard 50 m² @ ₱110k/yr</span>
                            </div>

                            <!-- Other Production Expenses -->
                            <div>
                                <label for="other_expenses" class="block text-[10px] font-bold text-gray-700 uppercase tracking-wider mb-1">
                                    OTHER EXPENSES (₱)
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2 text-xs font-bold text-gray-500">₱</span>
                                    <input type="number" step="100" min="0"
                                        class="w-full bg-white border border-drive-border rounded-xl ps-7 pe-3 py-2 text-sm font-semibold text-dark focus:outline-none focus:border-drive-primary"
                                        id="other_expenses" name="other_expenses" placeholder="0" value="0">
                                </div>
                                <span class="text-[10px] text-muted">Seeds, water, fertilizer</span>
                            </div>
                        </div>

                        <!-- Live Calculation Result Card -->
                        <div class="mt-3 p-2.5 rounded-xl bg-white border border-emerald-300 d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-[10px] font-bold text-emerald-800 uppercase tracking-wider d-block">ESTIMATED NET GAIN</span>
                                <span class="text-xs text-muted" id="calcEquationPreview">₱200,000 − ₱110,000 =</span>
                            </div>
                            <div class="text-end">
                                <span class="fs-5 fw-bold text-success" id="calcNetProfitPreview">+₱90,000.00</span>
                                <span class="d-block text-[10px] text-success fw-bold">Net Profit</span>
                            </div>
                        </div>
                    </div>

                    <!-- Harvest Date & Notes -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                        <div>
                            <label for="harvest_date" class="block text-[10px] font-bold text-drive-text-sub uppercase tracking-wider mb-1.5">
                                HARVEST DATE
                            </label>
                            <input type="date"
                                class="w-full bg-white border border-drive-border rounded-xl px-3.5 py-2 text-sm focus:outline-none focus:border-drive-primary"
                                id="harvest_date" name="harvest_date" required value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div>
                            <label for="notes" class="block text-[10px] font-bold text-drive-text-sub uppercase tracking-wider mb-1.5">
                                OBSERVATIONS / NOTES
                            </label>
                            <input type="text"
                                class="w-full bg-white border border-drive-border rounded-xl px-3.5 py-2 text-sm focus:outline-none focus:border-drive-primary"
                                id="notes" name="notes" placeholder="Grade A organic yield, high sugar brix...">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-2">
                        <button type="button" class="btn btn-drive-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-drive-primary rounded-pill px-4 font-semibold">Record Yield &amp; Profit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo $base_path; ?>assets/js/harvests.js"></script>

<?php include '../includes/footer.php'; ?>