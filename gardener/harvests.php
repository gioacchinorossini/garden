<?php
$base_path = '../';
$page_title = "Record Harvests";
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
?>

<main class="flex-1 bg-white rounded-[24px] border border-drive-border flex flex-col overflow-hidden h-full">
    <!-- Toolbar/Title Bar -->
    <div class="h-14 border-b border-drive-border px-6 flex items-center justify-between flex-shrink-0 bg-white">
        <div>
            <h1 class="text-sm font-bold text-drive-text-main flex items-center gap-2">
                <i class="bi bi-basket3 text-drive-primary"></i> My Harvest Log
            </h1>
        </div>
        <div class="flex items-center gap-3">
            <!-- Table vs Grid Toggler -->
            <div class="flex items-center gap-1 border border-drive-border rounded-full p-0.5 bg-drive-canvas">
                <button id="listViewBtn" onclick="switchView('list')"
                    class="px-3 py-1 rounded-full text-xs font-bold flex items-center gap-1.5 transition-all bg-white text-drive-primary ">
                    <i class="bi bi-list-task"></i> List
                </button>
                <button id="gridViewBtn" onclick="switchView('grid')"
                    class="px-3 py-1 rounded-full text-xs font-semibold flex items-center gap-1.5 transition-all text-drive-text-sub hover:bg-drive-surface-hover">
                    <i class="bi bi-grid-fill"></i> Grid
                </button>
            </div>

            <button
                class="bg-drive-primary hover:bg-drive-primary-hover text-white font-semibold py-1.5 px-4 rounded-full text-xs transition-colors flex items-center gap-1.5 "
                data-bs-toggle="modal" data-bs-target="#recordHarvestModal">
                <i class="bi bi-plus-lg"></i> Record Harvest
            </button>
        </div>
    </div>

    <!-- Workspace Scrollable Area -->
    <div class="flex-1 p-6 overflow-y-auto bg-white">
        <!-- Alert Placement -->
        <div id="alertPlaceholder"></div>

        <!-- Empty State Container -->
        <div id="harvestsEmptyState"
            class="hidden text-center py-16 text-drive-text-muted border border-dashed border-drive-border rounded-2xl">
            <i class="bi bi-basket3 text-4xl d-block mb-3 text-drive-primary"></i>
            <p class="text-sm font-semibold text-drive-text-main mb-1">No harvest records logged yet</p>
            <p class="text-xs text-drive-text-sub max-w-sm mx-auto mb-4">Keep a history of your garden production
                yields, weights, and observations to track land productivity.</p>
            <button
                class="bg-drive-primary hover:bg-drive-primary-hover text-white font-semibold py-2 px-5 rounded-full text-xs"
                data-bs-toggle="modal" data-bs-target="#recordHarvestModal">
                Log Your First Harvest
            </button>
        </div>

        <!-- Content Wrapper (Contains List and Grid) -->
        <div id="harvestsContentWrapper" class="hidden flex flex-col gap-6">
            <!-- 1. List View Container -->
            <div id="harvestListView" class="border border-drive-border rounded-2xl bg-white overflow-hidden ">
                <!-- Header Row -->
                <div
                    class="flex align-items-center justify-between px-6 py-3 bg-drive-canvas border-b border-drive-border text-[10px] font-bold text-drive-text-sub uppercase tracking-wider">
                    <div class="w-1/4">Crop Type</div>
                    <div class="w-1/6">Garden Plot</div>
                    <div class="w-1/3">Observations & Notes</div>
                    <div class="w-1/6">Harvest Date</div>
                    <div class="w-1/12 text-right">Total Yield</div>
                </div>

                <!-- Harvest Rows Placeholder -->
                <div id="harvestTableBody" class="divide-y divide-drive-border"></div>
            </div>

            <!-- 2. Grid View Container -->
            <div id="harvestGridView" class="hidden grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"></div>
        </div>
    </div>
</main>

<!-- Record Harvest Modal -->
<div class="modal fade" id="recordHarvestModal" tabindex="-1" aria-hidden="true" style="z-index: 9999;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content drive-modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold text-dark">Log Crop Harvest Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-start">
                <form id="recordHarvestForm">
                    <div class="mb-3">
                        <label for="plot_num"
                            class="block text-[10px] font-bold text-drive-text-sub uppercase tracking-wider mb-2">SOURCE
                            PLOT</label>
                        <select
                            class="w-full bg-white border border-drive-border rounded-xl px-4 py-2 text-sm focus:outline-none focus:border-drive-primary"
                            id="plot_num" name="plot_num" required>
                            <option value="Plot B-1">Downtown Rooftop Garden - Plot B-1</option>
                            <option value="Plot B-2">Downtown Rooftop Garden - Plot B-2</option>
                            <option value="Plot E-1">Eastside Clay Meadows - Plot E-1</option>
                            <option value="Plot R-1">Riverdale Acres - Plot R-1</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
                        <div class="md:col-span-2">
                            <label for="crop_name"
                                class="block text-[10px] font-bold text-drive-text-sub uppercase tracking-wider mb-2">CROP
                                NAME / PRODUCE</label>
                            <input type="text"
                                class="w-full bg-white border border-drive-border rounded-xl px-4 py-2 text-sm focus:outline-none focus:border-drive-primary"
                                id="crop_name" name="crop_name" required placeholder="e.g. Organic Tomatoes">
                        </div>
                        <div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label for="quantity"
                                        class="block text-[10px] font-bold text-drive-text-sub uppercase tracking-wider mb-2">QTY</label>
                                    <input type="number" step="0.1"
                                        class="w-full bg-white border border-drive-border rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-drive-primary"
                                        id="quantity" name="quantity" required placeholder="10">
                                </div>
                                <div>
                                    <label for="unit"
                                        class="block text-[10px] font-bold text-drive-text-sub uppercase tracking-wider mb-2">UNIT</label>
                                    <select
                                        class="w-full bg-white border border-drive-border rounded-xl px-2 py-2 text-sm focus:outline-none focus:border-drive-primary"
                                        id="unit" name="unit" required>
                                        <option value="kg">kg</option>
                                        <option value="pcs">pcs</option>
                                        <option value="bunches">bunches</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="harvest_date"
                            class="block text-[10px] font-bold text-drive-text-sub uppercase tracking-wider mb-2">HARVEST
                            DATE</label>
                        <input type="date"
                            class="w-full bg-white border border-drive-border rounded-xl px-4 py-2 text-sm focus:outline-none focus:border-drive-primary"
                            id="harvest_date" name="harvest_date" required value="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div class="mb-4">
                        <label for="notes"
                            class="block text-[10px] font-bold text-drive-text-sub uppercase tracking-wider mb-2">OBSERVATIONS
                            & NOTES</label>
                        <textarea
                            class="w-full bg-white border border-drive-border rounded-xl px-4 py-2 text-sm focus:outline-none focus:border-drive-primary"
                            id="notes" name="notes" rows="3"
                            placeholder="Describe crop quality, pests encountered, or sharing methods..."></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-drive-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-drive-primary">Record Yield</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo $base_path; ?>assets/js/harvests.js"></script>

<?php include '../includes/footer.php'; ?>