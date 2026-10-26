<?php
$base_path = '../';
$page_title = "Menu & Sales";
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
?>

<main class="workspace-surface flex-1 flex flex-col overflow-hidden h-full">
    <!-- Toolbar/Title Bar -->
    <div class="h-14 px-6 flex items-center justify-between flex-shrink-0 bg-white border-b border-drive-border">
        <div class="flex items-center gap-3">
            <h1 class="text-sm font-bold text-drive-text-main flex items-center gap-2">
                <i data-lucide="utensils" class="text-orange-600" style="width: 18px; height: 18px;"></i> Street Food Menu & Sales
            </h1>
            <!-- Tab Pills -->
            <div class="flex items-center gap-1 bg-drive-canvas p-1 rounded-full border border-drive-border text-xs">
                <button id="tabMenuBtn" onclick="switchVendorTab('menu')"
                    class="px-3.5 py-1 rounded-full font-bold transition-all bg-white text-drive-primary shadow-xs">
                    Food Menu
                </button>
                <button id="tabSalesBtn" onclick="switchVendorTab('sales')"
                    class="px-3.5 py-1 rounded-full font-semibold transition-all text-drive-text-sub hover:bg-drive-surface-hover">
                    Sales Logs
                </button>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button id="actionBtnMenu"
                class="bg-drive-primary hover:bg-drive-primary-hover active:scale-95 text-white font-semibold py-1.5 px-4 rounded-full text-xs transition-all flex items-center gap-1.5 shadow-sm"
                data-bs-toggle="modal" data-bs-target="#addMenuItemModal">
                <i data-lucide="plus" style="width: 14px; height: 14px;"></i> Add Menu Item
            </button>
            <button id="actionBtnSale"
                class="hidden bg-drive-primary hover:bg-drive-primary-hover active:scale-95 text-white font-semibold py-1.5 px-4 rounded-full text-xs transition-all items-center gap-1.5 shadow-sm"
                data-bs-toggle="modal" data-bs-target="#recordSaleModal">
                <i data-lucide="plus" style="width: 14px; height: 14px;"></i> Record Sale
            </button>
        </div>
    </div>

    <!-- Workspace Scrollable Area -->
    <div class="flex-1 p-6 overflow-y-auto bg-drive-canvas/20">

        <!-- Global Alert -->
        <div id="alertPlaceholder"></div>

        <!-- ========================================== -->
        <!-- TAB 1: STREET FOOD MENU                     -->
        <!-- ========================================== -->
        <div id="sectionMenu" class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-drive-text-main font-['Outfit']">Your Food Offerings</h2>
                    <p class="text-xs text-drive-text-muted">Street foods and beverages displayed on your stall profile and public map</p>
                </div>
            </div>

            <!-- Menu Items Grid -->
            <div id="menuItemsGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                <!-- Loaded via JS -->
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 2: SALES LOGS                           -->
        <!-- ========================================== -->
        <div id="sectionSales" class="hidden space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-drive-text-main font-['Outfit']">Daily Sales Records</h2>
                    <p class="text-xs text-drive-text-muted">Track quantities sold and daily earnings across your stalls</p>
                </div>
            </div>

            <!-- Sales Table -->
            <div class="border border-drive-border rounded-2xl bg-white overflow-hidden shadow-xs">
                <div class="flex items-center justify-between px-6 py-3 bg-drive-canvas border-b border-drive-border text-[10px] font-bold text-drive-text-sub uppercase tracking-wider">
                    <div class="w-1/4">Food Item</div>
                    <div class="w-1/6">Qty Sold</div>
                    <div class="w-1/4">Date</div>
                    <div class="w-1/4">Notes</div>
                    <div class="w-1/6 text-right">Total Revenue</div>
                </div>
                <div id="salesTableBody" class="divide-y divide-drive-border">
                    <!-- Loaded via JS -->
                </div>
            </div>
        </div>

    </div>
</main>

<!-- Modal 1: Add Menu Item -->
<div class="modal fade" id="addMenuItemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border border-drive-border rounded-3xl p-3 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title font-bold text-base text-drive-text-main">Add Street Food Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-start">
                <form id="addMenuItemForm" class="space-y-3.5">
                    <div>
                        <label for="item_name" class="block text-[10px] font-bold text-drive-text-sub uppercase tracking-wider mb-1">Item Name</label>
                        <input type="text" id="item_name" name="item_name" required
                            class="w-full bg-white border border-drive-border rounded-xl px-4 py-2 text-sm focus:outline-none focus:border-drive-primary"
                            placeholder="e.g. Special Pork Isaw, Calamansi Juice">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="category" class="block text-[10px] font-bold text-drive-text-sub uppercase tracking-wider mb-1">Category</label>
                            <select id="category" name="category" required
                                class="w-full bg-white border border-drive-border rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-drive-primary">
                                <option value="Skewers & Grills">Skewers & Grills</option>
                                <option value="Fried Street Food">Fried Street Food</option>
                                <option value="Beverages & Coolers">Beverages & Coolers</option>
                                <option value="Desserts & Sweets">Desserts & Sweets</option>
                                <option value="Rice Meals">Rice Meals</option>
                            </select>
                        </div>
                        <div>
                            <label for="price" class="block text-[10px] font-bold text-drive-text-sub uppercase tracking-wider mb-1">Price (₱)</label>
                            <input type="number" step="0.5" id="price" name="price" required
                                class="w-full bg-white border border-drive-border rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-drive-primary"
                                placeholder="25.00">
                        </div>
                    </div>

                    <div>
                        <label for="item_desc" class="block text-[10px] font-bold text-drive-text-sub uppercase tracking-wider mb-1">Description</label>
                        <textarea id="item_desc" name="description" rows="2"
                            class="w-full bg-white border border-drive-border rounded-xl px-4 py-2 text-sm focus:outline-none focus:border-drive-primary"
                            placeholder="Marination, ingredients, or dip details..."></textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" class="btn btn-sm btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="bg-drive-primary hover:bg-drive-primary-hover text-white text-xs font-semibold px-5 py-2 rounded-full shadow-xs">Save Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal 2: Record Daily Sale -->
<div class="modal fade" id="recordSaleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border border-drive-border rounded-3xl p-3 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title font-bold text-base text-drive-text-main">Record Daily Sale</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-start">
                <form id="recordSaleForm" class="space-y-3.5">
                    <div>
                        <label for="sale_item_name" class="block text-[10px] font-bold text-drive-text-sub uppercase tracking-wider mb-1">Food Item</label>
                        <input type="text" id="sale_item_name" name="item_name" required
                            class="w-full bg-white border border-drive-border rounded-xl px-4 py-2 text-sm focus:outline-none focus:border-drive-primary"
                            placeholder="e.g. Special Pork Isaw">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="quantity_sold" class="block text-[10px] font-bold text-drive-text-sub uppercase tracking-wider mb-1">Quantity Sold</label>
                            <input type="number" id="quantity_sold" name="quantity_sold" required min="1"
                                class="w-full bg-white border border-drive-border rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-drive-primary"
                                placeholder="50">
                        </div>
                        <div>
                            <label for="total_revenue" class="block text-[10px] font-bold text-drive-text-sub uppercase tracking-wider mb-1">Total Revenue (₱)</label>
                            <input type="number" step="0.5" id="total_revenue" name="total_revenue" required
                                class="w-full bg-white border border-drive-border rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-drive-primary"
                                placeholder="1000.00">
                        </div>
                    </div>

                    <div>
                        <label for="sale_date" class="block text-[10px] font-bold text-drive-text-sub uppercase tracking-wider mb-1">Date</label>
                        <input type="date" id="sale_date" name="log_date" required value="<?php echo date('Y-m-d'); ?>"
                            class="w-full bg-white border border-drive-border rounded-xl px-4 py-2 text-sm focus:outline-none focus:border-drive-primary">
                    </div>

                    <div>
                        <label for="sale_notes" class="block text-[10px] font-bold text-drive-text-sub uppercase tracking-wider mb-1">Notes (Optional)</label>
                        <input type="text" id="sale_notes" name="notes"
                            class="w-full bg-white border border-drive-border rounded-xl px-4 py-2 text-sm focus:outline-none focus:border-drive-primary"
                            placeholder="Night market peak hour, rain, etc.">
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" class="btn btn-sm btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="bg-drive-primary hover:bg-drive-primary-hover text-white text-xs font-semibold px-5 py-2 rounded-full shadow-xs">Log Sale</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
let currentTab = 'menu';

function switchVendorTab(tab) {
    currentTab = tab;
    const tabMenuBtn = document.getElementById('tabMenuBtn');
    const tabSalesBtn = document.getElementById('tabSalesBtn');
    const secMenu = document.getElementById('sectionMenu');
    const secSales = document.getElementById('sectionSales');
    const btnMenu = document.getElementById('actionBtnMenu');
    const btnSale = document.getElementById('actionBtnSale');

    if (tab === 'menu') {
        tabMenuBtn.className = "px-3.5 py-1 rounded-full font-bold transition-all bg-white text-drive-primary shadow-xs";
        tabSalesBtn.className = "px-3.5 py-1 rounded-full font-semibold transition-all text-drive-text-sub hover:bg-drive-surface-hover";
        secMenu.classList.remove('hidden');
        secSales.classList.add('hidden');
        btnMenu.classList.remove('hidden');
        btnSale.classList.add('hidden');
    } else {
        tabSalesBtn.className = "px-3.5 py-1 rounded-full font-bold transition-all bg-white text-drive-primary shadow-xs";
        tabMenuBtn.className = "px-3.5 py-1 rounded-full font-semibold transition-all text-drive-text-sub hover:bg-drive-surface-hover";
        secSales.classList.remove('hidden');
        secMenu.classList.add('hidden');
        btnSale.classList.remove('hidden');
        btnSale.classList.add('d-flex');
        btnMenu.classList.add('hidden');
    }
}

async function loadMenuAndSales() {
    try {
        const res = await fetch('../api/harvests.php');
        const json = await res.json();
        if (json.status === 'success') {
            renderMenuItems(json.data.menu_items || []);
            renderSalesLogs(json.data.sales_logs || []);
        }
    } catch (e) {
        console.error("Error loading menu:", e);
    }
}

function renderMenuItems(items) {
    const grid = document.getElementById('menuItemsGrid');
    if (!items.length) {
        grid.innerHTML = `
            <div class="col-span-full py-12 text-center bg-white border border-dashed border-drive-border rounded-3xl p-8">
                <i class="bi bi-egg-fried text-4xl text-orange-400 mb-2 block"></i>
                <h6 class="font-bold text-sm text-drive-text-main">No Menu Items Added Yet</h6>
                <p class="text-xs text-drive-text-muted mb-4">Click "Add Menu Item" above to post your street food offerings.</p>
            </div>
        `;
        return;
    }

    grid.innerHTML = items.map(item => `
        <div class="bg-white border border-drive-border rounded-2xl p-4 shadow-xs hover:shadow-sm transition-all flex flex-col justify-between">
            <div>
                <div class="flex items-start justify-between mb-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-orange-50 text-orange-700">
                        ${escapeHtml(item.category || 'Street Food')}
                    </span>
                    <span class="text-xs font-bold text-emerald-700">
                        ${item.is_available ? '● Available' : '<span class="text-gray-400">● Sold Out</span>'}
                    </span>
                </div>
                <h3 class="font-bold text-sm text-drive-text-main mb-1">${escapeHtml(item.item_name)}</h3>
                <p class="text-xs text-drive-text-muted line-clamp-2 mb-3">${escapeHtml(item.description || 'Freshly cooked and served hot.')}</p>
            </div>
            <div class="pt-3 border-t border-drive-border flex items-center justify-between">
                <span class="text-base font-extrabold text-drive-text-main font-['Outfit']">₱${parseFloat(item.price).toFixed(2)}</span>
                <button onclick="toggleAvailability(${item.id}, ${item.is_available ? 0 : 1})"
                    class="text-[11px] font-semibold text-drive-primary hover:underline">
                    ${item.is_available ? 'Mark Sold Out' : 'Mark Available'}
                </button>
            </div>
        </div>
    `).join('');
}

function renderSalesLogs(logs) {
    const body = document.getElementById('salesTableBody');
    if (!logs.length) {
        body.innerHTML = `
            <div class="py-8 text-center text-xs text-drive-text-muted">No sales logged yet. Record your daily earnings above!</div>
        `;
        return;
    }

    body.innerHTML = logs.map(l => `
        <div class="flex items-center justify-between px-6 py-3 text-xs text-drive-text-main hover:bg-drive-canvas/40 transition-colors">
            <div class="w-1/4 font-semibold">${escapeHtml(l.item_name)}</div>
            <div class="w-1/6 font-bold text-drive-text-sub">${l.quantity_sold} pcs</div>
            <div class="w-1/4 text-drive-text-muted">${escapeHtml(l.log_date)}</div>
            <div class="w-1/4 text-drive-text-muted truncate">${escapeHtml(l.notes || '—')}</div>
            <div class="w-1/6 text-right font-extrabold text-drive-primary">₱${parseFloat(l.total_revenue).toFixed(2)}</div>
        </div>
    `).join('');
}

async function toggleAvailability(id, isAvail) {
    try {
        await fetch('../api/harvests.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'toggle_availability', item_id: id, is_available: isAvail })
        });
        loadMenuAndSales();
    } catch(e) {
        alert("Error updating availability");
    }
}

// Handlers for Form Submissions
document.getElementById('addMenuItemForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const data = {
        action: 'add_menu_item',
        item_name: document.getElementById('item_name').value,
        category: document.getElementById('category').value,
        price: document.getElementById('price').value,
        description: document.getElementById('item_desc').value
    };
    const res = await fetch('../api/harvests.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    });
    const result = await res.json();
    if (result.status === 'success') {
        bootstrap.Modal.getInstance(document.getElementById('addMenuItemModal')).hide();
        this.reset();
        loadMenuAndSales();
    } else {
        alert(result.message || "Error adding item");
    }
});

document.getElementById('recordSaleForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const data = {
        action: 'record_sale',
        item_name: document.getElementById('sale_item_name').value,
        quantity_sold: document.getElementById('quantity_sold').value,
        total_revenue: document.getElementById('total_revenue').value,
        log_date: document.getElementById('sale_date').value,
        notes: document.getElementById('sale_notes').value
    };
    const res = await fetch('../api/harvests.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    });
    const result = await res.json();
    if (result.status === 'success') {
        bootstrap.Modal.getInstance(document.getElementById('recordSaleModal')).hide();
        this.reset();
        loadMenuAndSales();
    } else {
        alert(result.message || "Error recording sale");
    }
});

document.addEventListener('DOMContentLoaded', loadMenuAndSales);
</script>

<?php include '../includes/footer.php'; ?>