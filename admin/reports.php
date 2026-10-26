<?php
$base_path = '../';
$page_title = "Platform Reports & Analytics";
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
?>

<main class="workspace-surface">
    <!-- Toolbar/Title Bar -->
    <div class="toolbar border-bottom">
        <div>
            <h1 class="fs-5 fw-semibold m-0 text-dark">Platform Reports & Analytics</h1>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-outline-secondary rounded-pill btn-sm px-3" onclick="window.print()">
                <i class="bi bi-printer-fill me-1"></i> Print
            </button>
            <button class="btn btn-drive-primary btn-sm px-3">
                <i class="bi bi-download me-1"></i> Export CSV
            </button>
        </div>
    </div>

    <!-- Workspace Scrollable Area -->
    <div class="workspace-scroll">
        <!-- 1. Grid of Highlights -->
        <div class="row g-3 mb-4">
            <!-- Metric Card 1: Stall Occupancy -->
            <div class="col-md-4">
                <div class="drive-card">
                    <span class="text-secondary fw-semibold" style="font-size: 0.75rem; letter-spacing:0.5px;">STALL OCCUPANCY RATE</span>
                    <div class="d-flex align-items-center justify-content-between mt-2">
                        <h3 class="fw-bold m-0" style="color: var(--drive-primary);">81.8%</h3>
                        <span class="text-success" style="font-size: 0.8rem; font-weight:600;"><i
                                class="bi bi-caret-up-fill"></i> +5.4%</span>
                    </div>
                    <div class="w-full bg-[#e1e3e1] h-2 rounded-full overflow-hidden mt-3 mb-1"
                        style="height: 6px; background-color: var(--drive-border);">
                        <div class="bg-primary h-full"
                            style="width: 81.8%; height:100%; background-color: var(--drive-primary) !important;"></div>
                    </div>
                    <small class="text-muted" style="font-size: 0.7rem;">18 leased / 22 total stalls</small>
                </div>
            </div>

            <!-- Metric Card 2: Active Vendors -->
            <div class="col-md-4">
                <div class="drive-card">
                    <span class="text-secondary fw-semibold" style="font-size: 0.75rem; letter-spacing:0.5px;">ACTIVE STREET VENDORS</span>
                    <div class="d-flex align-items-center justify-content-between mt-2">
                        <h3 class="fw-bold m-0" style="color: var(--drive-text-main);">14 Vendors</h3>
                        <span class="text-success" style="font-size: 0.8rem; font-weight:600;"><i
                                class="bi bi-caret-up-fill"></i> +3 this month</span>
                    </div>
                    <div class="w-full bg-[#e1e3e1] h-2 rounded-full overflow-hidden mt-3 mb-1"
                        style="height: 6px; background-color: var(--drive-border);">
                        <div class="bg-success h-full" style="width: 78%; height:100%;"></div>
                    </div>
                    <small class="text-muted" style="font-size: 0.7rem;">Across 5 Food Hub Properties</small>
                </div>
            </div>

            <!-- Metric Card 3: Avg Vendor Revenue -->
            <div class="col-md-4">
                <div class="drive-card">
                    <span class="text-secondary fw-semibold" style="font-size: 0.75rem; letter-spacing:0.5px;">AVG SALES / STALL</span>
                    <div class="d-flex align-items-center justify-content-between mt-2">
                        <h3 class="fw-bold m-0 text-success">₱34,500</h3>
                        <span class="text-success" style="font-size: 0.8rem; font-weight:600;"><i
                                class="bi bi-caret-up-fill"></i> +12.3%</span>
                    </div>
                    <div class="w-full bg-[#e1e3e1] h-2 rounded-full overflow-hidden mt-3 mb-1"
                        style="height: 6px; background-color: var(--drive-border);">
                        <div class="bg-success h-full" style="width: 85%; height:100%;"></div>
                    </div>
                    <small class="text-muted" style="font-size: 0.7rem;">Monthly average per active stall</small>
                </div>
            </div>
        </div>

        <!-- 2. Breakdown and Milestones -->
        <div class="row g-4 mb-4">
            <!-- Left Graph: Street Food Sales by Category -->
            <div class="col-md-6">
                <div class="card border rounded-4 p-4" style="border-color: var(--drive-border) !important;">
                    <h2 class="fs-6 fw-semibold text-secondary mb-3"
                        style="letter-spacing: 0.5px; text-transform: uppercase;">Sales by Cuisine Specialty</h2>

                    <!-- Category 1 -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1 align-items-center" style="font-size: 0.8rem;">
                            <span class="d-flex align-items-center gap-1.5"><i data-lucide="flame" class="text-danger" style="width: 16px; height: 16px;"></i> Grilled BBQ & Skewers</span>
                            <strong>41.2% (₱142,000)</strong>
                        </div>
                        <div class="progress" style="height: 12px; border-radius: 50rem;">
                            <div class="progress-bar bg-danger" role="progressbar"
                                style="width: 41.2%; border-radius: 50rem;" aria-valuenow="41.2" aria-valuemin="0"
                                aria-valuemax="100"></div>
                        </div>
                    </div>

                    <!-- Category 2 -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1 align-items-center" style="font-size: 0.8rem;">
                            <span class="d-flex align-items-center gap-1.5"><i data-lucide="utensils" class="text-warning" style="width: 16px; height: 16px;"></i> Deep Fried Street Bites (Kwek-Kwek, Calamares)</span>
                            <strong>27.9% (₱96,200)</strong>
                        </div>
                        <div class="progress" style="height: 12px; border-radius: 50rem;">
                            <div class="progress-bar bg-warning" role="progressbar"
                                style="width: 27.9%; border-radius: 50rem;" aria-valuenow="27.9" aria-valuemin="0"
                                aria-valuemax="100"></div>
                        </div>
                    </div>

                    <!-- Category 3 -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1 align-items-center" style="font-size: 0.8rem;">
                            <span class="d-flex align-items-center gap-1.5"><i data-lucide="soup" class="text-primary" style="width: 16px; height: 16px;"></i> Takoyaki, Dimsum & Noodles</span>
                            <strong>19.1% (₱65,800)</strong>
                        </div>
                        <div class="progress" style="height: 12px; border-radius: 50rem;">
                            <div class="progress-bar bg-primary" role="progressbar"
                                style="width: 19.1%; border-radius: 50rem;" aria-valuenow="19.1" aria-valuemin="0"
                                aria-valuemax="100"></div>
                        </div>
                    </div>

                    <!-- Category 4 -->
                    <div class="mb-2">
                        <div class="d-flex justify-content-between mb-1 align-items-center" style="font-size: 0.8rem;">
                            <span class="d-flex align-items-center gap-1.5"><i data-lucide="coffee" class="text-info" style="width: 16px; height: 16px;"></i> Gulaman, Milk Tea & Fruit Shakes</span>
                            <strong>11.8% (₱40,700)</strong>
                        </div>
                        <div class="progress" style="height: 12px; border-radius: 50rem;">
                            <div class="progress-bar bg-info" role="progressbar"
                                style="width: 11.8%; border-radius: 50rem;" aria-valuenow="11.8" aria-valuemin="0"
                                aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: System Activity Timeline -->
            <div class="col-md-6">
                <div class="card border rounded-4 p-4 h-100" style="border-color: var(--drive-border) !important;">
                    <h2 class="fs-6 fw-semibold text-secondary mb-3"
                        style="letter-spacing: 0.5px; text-transform: uppercase;">Platform Milestones</h2>

                    <div class="position-relative ps-4 border-start"
                        style="border-color: var(--drive-border) !important; font-size: 0.825rem;">
                        <!-- Timeline Item 1 -->
                        <div class="mb-3 position-relative">
                            <span class="position-absolute bg-primary rounded-circle"
                                style="width: 10px; height: 10px; left: -29px; top: 5px;"></span>
                            <strong class="text-dark d-block">Weekend Food Bazaar Surge</strong>
                            <span class="text-muted d-block" style="font-size:0.75rem;">Oct 2026 • 18 vendor stalls active</span>
                        </div>
                        <!-- Timeline Item 2 -->
                        <div class="mb-3 position-relative">
                            <span class="position-absolute bg-success rounded-circle"
                                style="width: 10px; height: 10px; left: -29px; top: 5px;"></span>
                            <strong class="text-dark d-block">Roxas Boulevard Food Park Added</strong>
                            <span class="text-muted d-block" style="font-size:0.75rem;">Sep 2026 • 8 prime stalls allocated</span>
                        </div>
                        <!-- Timeline Item 3 -->
                        <div class="position-relative">
                            <span class="position-absolute bg-secondary rounded-circle"
                                style="width: 10px; height: 10px; left: -29px; top: 5px;"></span>
                            <strong class="text-dark d-block">StreetSpot Hub Platform Launch</strong>
                            <span class="text-muted d-block" style="font-size:0.75rem;">Aug 2026 • Commercial vendor & property portal live</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Top Performing Street Food Items -->
        <h2 class="fs-6 fw-semibold text-secondary mb-3" style="letter-spacing: 0.5px; text-transform: uppercase;">Top Performing Menu Offerings</h2>
        <div class="border rounded-4 bg-white overflow-hidden border-light-subtle mb-4">
            <!-- Header Row -->
            <div class="d-flex align-items-center justify-content-between px-4 py-2 bg-light border-bottom text-secondary"
                style="font-size: 0.75rem; font-weight: 600;">
                <div class="w-25">MENU ITEM</div>
                <div class="w-25">STREET VENDOR</div>
                <div class="w-25">PROPERTY LOCATION</div>
                <div class="w-15">MONTHLY UNITS</div>
                <div class="w-10 text-end">REVENUE</div>
            </div>

            <!-- Row 1 -->
            <div class="d-flex align-items-center justify-content-between px-4 py-2 border-bottom hover:bg-[#f8faff] text-sm text-dark transition-colors"
                style="height: 48px;">
                <div class="w-25 d-flex align-items-center gap-2 fw-semibold">
                    <i data-lucide="flame" class="text-danger" style="width: 16px; height: 16px;"></i> Pork BBQ Skewers (3 pcs)
                </div>
                <div class="w-25 text-secondary">Kusina Street Bites</div>
                <div class="w-25 text-secondary">Roxas Boulevard Food Park</div>
                <div class="w-15 fw-bold text-dark">520 orders</div>
                <div class="w-10 text-end fw-bold text-success">₱41,600</div>
            </div>

            <!-- Row 2 -->
            <div class="d-flex align-items-center justify-content-between px-4 py-2 border-bottom hover:bg-[#f8faff] text-sm text-dark transition-colors"
                style="height: 48px;">
                <div class="w-25 d-flex align-items-center gap-2 fw-semibold">
                    <i data-lucide="utensils" class="text-warning" style="width: 16px; height: 16px;"></i> Crispy Chicken Isaw
                </div>
                <div class="w-25 text-secondary">Kusina Street Bites</div>
                <div class="w-25 text-secondary">Roxas Boulevard Food Park</div>
                <div class="w-15 fw-bold text-dark">610 orders</div>
                <div class="w-10 text-end fw-bold text-success">₱24,400</div>
            </div>

            <!-- Row 3 -->
            <div class="d-flex align-items-center justify-content-between px-4 py-2 border-bottom hover:bg-[#f8faff] text-sm text-dark transition-colors"
                style="height: 48px;">
                <div class="w-25 d-flex align-items-center gap-2 fw-semibold">
                    <i data-lucide="soup" class="text-primary" style="width: 16px; height: 16px;"></i> Authentic Takoyaki (8 pcs)
                </div>
                <div class="w-25 text-secondary">Tokyo Street Bites</div>
                <div class="w-25 text-secondary">Makati Avenue Corner Lot</div>
                <div class="w-15 fw-bold text-dark">340 orders</div>
                <div class="w-10 text-end fw-bold text-success">₱37,400</div>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>