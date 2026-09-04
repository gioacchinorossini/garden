<?php
// Modern Botanical Studio UI for Landowner
?>
<main class="modern-workspace p-4">
    <!-- Hero Banner -->
    <div class="modern-hero-banner mb-4">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <span class="badge bg-white text-dark rounded-pill px-3 py-1 text-uppercase mb-2 font-semibold" style="font-size: 0.7rem; letter-spacing: 1px;">
                    <i class="bi bi-stars text-success me-1"></i> Landowner Botanical Studio V2
                </span>
                <h1 class="fw-bold mb-2 text-white" style="font-size: 1.75rem;">Welcome back, John!</h1>
                <p class="mb-0 text-white-50 text-sm">
                    Manage your land assets, review gardener plot applications, and monitor community agricultural yield in real-time.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                    <a href="register.php" class="btn btn-light text-success fw-bold rounded-pill px-3 py-2 shadow-sm d-inline-flex align-items-center gap-2 text-sm">
                        <i class="bi bi-plus-circle-fill"></i>
                        <span>Register Property</span>
                    </a>
                    <button class="btn btn-outline-light rounded-pill px-3 py-2 text-sm" data-bs-toggle="modal" data-bs-target="#plotMatrixModal">
                        <i class="bi bi-grid-3x3-gap-fill me-1"></i> Quick Matrix
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 3 Key Metric Cards Row -->
    <div class="row g-3 mb-4">
        <!-- Metric 1: My Properties -->
        <div class="col-md-4">
            <div class="stat-card-v2">
                <div class="stat-icon-wrapper" style="background-color: #ecfdf5; color: #059669;">
                    <i class="bi bi-geo-alt-fill"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="text-muted fw-semibold text-xs text-uppercase">Properties Managed</span>
                        <span class="badge badge-emerald">Active</span>
                    </div>
                    <h3 class="fw-bold m-0 mt-1" style="font-size: 1.75rem; color: #0f172a;">3 <span class="text-xs fw-normal text-muted">Estates</span></h3>
                    <div class="crop-progress-track mt-2">
                        <div class="crop-progress-fill" style="width: 67%;"></div>
                    </div>
                    <span class="text-xs text-muted mt-1 d-block">8 of 12 plots occupied (67%)</span>
                </div>
            </div>
        </div>

        <!-- Metric 2: Pending Applications -->
        <div class="col-md-4">
            <div class="stat-card-v2" style="border-left: 4px solid #f59e0b;">
                <div class="stat-icon-wrapper" style="background-color: #fef3c7; color: #d97706;">
                    <i class="bi bi-person-fill-exclamation"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="text-muted fw-semibold text-xs text-uppercase">Plot Applications</span>
                        <span class="badge badge-amber">Action Req</span>
                    </div>
                    <h3 class="fw-bold m-0 mt-1 text-warning-emphasis" style="font-size: 1.75rem;">1 <span class="text-xs fw-normal text-muted">Pending</span></h3>
                    <span class="text-xs text-muted mt-2 d-block">
                        <i class="bi bi-clock me-1"></i> Mary Gardener applied 2h ago
                    </span>
                </div>
            </div>
        </div>

        <!-- Metric 3: Community Yield & Carbon Offset -->
        <div class="col-md-4">
            <div class="stat-card-v2">
                <div class="stat-icon-wrapper" style="background-color: #eff6ff; color: #2563eb;">
                    <i class="bi bi-flower2"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="text-muted fw-semibold text-xs text-uppercase">Total Harvest Split</span>
                        <span class="badge badge-blue">+18% MoM</span>
                    </div>
                    <h3 class="fw-bold m-0 mt-1" style="font-size: 1.75rem; color: #1e3a8a;">142.5 <span class="text-sm font-semibold">kg</span></h3>
                    <span class="text-xs text-muted mt-1 d-block">
                        <i class="bi bi-tree-fill text-success me-1"></i> 310 kg CO2 Environmental Offset
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Workspace Section: Properties Visual Grid & Applications Drawer -->
    <div class="row g-4 mb-4">
        <!-- Left 8 Cols: Interactive Properties Matrix -->
        <div class="col-lg-8">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h2 class="fw-bold m-0 text-slate-800" style="font-size: 1.15rem;">My Estate Directory</h2>
                    <p class="text-muted text-xs mb-0">Visual partition matrix of registered lands and active gardener leases</p>
                </div>
                <div class="btn-group btn-group-sm rounded-pill p-1 bg-white border">
                    <button class="btn btn-success btn-sm rounded-pill px-3 active">All (3)</button>
                    <button class="btn btn-light btn-sm rounded-pill px-3">Approved (2)</button>
                    <button class="btn btn-light btn-sm rounded-pill px-3">Pending (1)</button>
                </div>
            </div>

            <!-- Land Card 1: Downtown Rooftop Garden -->
            <div class="modern-card mb-3 p-3">
                <div class="d-flex flex-wrap align-items-center justify-content-between pb-3 border-bottom mb-3 gap-2">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 p-2 bg-emerald-100 text-emerald-700" style="background: #ecfdf5; color: #047857;">
                            <i class="bi bi-building fs-4"></i>
                        </div>
                        <div>
                            <h3 class="fw-bold text-dark mb-0" style="font-size: 1.1rem;">Downtown Rooftop Garden</h3>
                            <span class="text-xs text-muted"><i class="bi bi-geo-alt me-1"></i> 45 Main St, Business District • 85.5 m²</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge badge-emerald"><span class="pulse-dot emerald me-1"></span> Verified Approved</span>
                        <a href="lands.php" class="btn btn-sm btn-outline-success rounded-pill px-3 font-semibold">Manage Plots</a>
                    </div>
                </div>

                <!-- Plot Partition Matrix Bar -->
                <div class="mb-3">
                    <div class="d-flex align-items-center justify-content-between text-xs text-muted mb-1">
                        <span>Plot Allocation Matrix (4 Plots Total)</span>
                        <span class="font-semibold text-emerald-700">100% Occupied</span>
                    </div>
                    <div class="d-flex gap-2">
                        <div class="flex-fill bg-success text-white p-2 rounded-3 text-center text-xs font-semibold shadow-sm" title="Plot A-1: Romaine Lettuce">
                            Plot A-1 <span class="d-block text-white-50" style="font-size: 0.65rem;">Mary G.</span>
                        </div>
                        <div class="flex-fill bg-success text-white p-2 rounded-3 text-center text-xs font-semibold shadow-sm" title="Plot A-2: Tomatoes">
                            Plot A-2 <span class="d-block text-white-50" style="font-size: 0.65rem;">Mary G.</span>
                        </div>
                        <div class="flex-fill bg-success text-white p-2 rounded-3 text-center text-xs font-semibold shadow-sm" title="Plot B-1: Basil">
                            Plot B-1 <span class="d-block text-white-50" style="font-size: 0.65rem;">Alex R.</span>
                        </div>
                        <div class="flex-fill bg-success text-white p-2 rounded-3 text-center text-xs font-semibold shadow-sm" title="Plot B-2: Peppers">
                            Plot B-2 <span class="d-block text-white-50" style="font-size: 0.65rem;">David S.</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Land Card 2: Sunnyvale Gardening Lot -->
            <div class="modern-card mb-3 p-3">
                <div class="d-flex flex-wrap align-items-center justify-content-between pb-3 border-bottom mb-3 gap-2">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 p-2 text-amber-700" style="background: #fef3c7; color: #b45309;">
                            <i class="bi bi-tree fs-4"></i>
                        </div>
                        <div>
                            <h3 class="fw-bold text-dark mb-0" style="font-size: 1.1rem;">Sunnyvale Gardening Lot</h3>
                            <span class="text-xs text-muted"><i class="bi bi-geo-alt me-1"></i> 124 Green Ave, Sunnyvale • 250.0 m²</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge badge-amber"><i class="bi bi-hourglass-split me-1"></i> Admin Verification Pending</span>
                        <a href="lands.php" class="btn btn-sm btn-outline-secondary rounded-pill px-3 font-semibold">View Details</a>
                    </div>
                </div>

                <div class="p-3 bg-light rounded-3 d-flex align-items-center justify-content-between text-xs text-muted">
                    <span><i class="bi bi-info-circle me-1"></i> Currently under verification by system admin. Plots will open after approval.</span>
                    <span class="fw-bold text-dark">0 Active Leases</span>
                </div>
            </div>
        </div>

        <!-- Right 4 Cols: Pending Applications Drawer & Activity Stream -->
        <div class="col-lg-4">
            <!-- Application Review Card -->
            <div class="modern-card mb-4" style="background: linear-gradient(180deg, #ffffff 0%, #f0fdf4 100%);">
                <div class="d-flex align-items-center justify-content-between pb-2 border-bottom mb-3">
                    <h3 class="fw-bold text-dark m-0" style="font-size: 1rem;">
                        <i class="bi bi-file-earmark-person-fill text-success me-1"></i> New Request
                    </h3>
                    <span class="badge badge-amber">1 Pending</span>
                </div>

                <div class="action-drawer-card">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center font-bold" style="width: 40px; height: 40px; font-size: 14px;">
                            MG
                        </div>
                        <div>
                            <h4 class="fw-bold text-dark mb-0" style="font-size: 0.95rem;">Mary Gardener</h4>
                            <span class="text-xs text-muted"><i class="bi bi-star-fill text-warning me-1"></i> Experienced Gardener (4.9 Rating)</span>
                        </div>
                    </div>
                    <div class="bg-white p-2.5 rounded-3 border mb-3 text-xs">
                        <p class="mb-1"><strong>Requested Plot:</strong> Downtown Rooftop (Plot B-1)</p>
                        <p class="mb-1"><strong>Target Crop:</strong> Organic Heirlooms & Mint</p>
                        <p class="mb-0 text-muted"><strong>Duration:</strong> 6 Months • Starts Sept 2026</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="requests.php" class="btn btn-success btn-sm w-100 rounded-pill font-semibold">Approve Request</a>
                        <a href="requests.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">Review</a>
                    </div>
                </div>
            </div>

            <!-- Eco & Income Split Summary -->
            <div class="modern-card p-3">
                <h3 class="fw-bold text-dark mb-3" style="font-size: 1rem;">
                    <i class="bi bi-shield-check text-success me-1"></i> Eco & Harvest Agreement
                </h3>
                <div class="d-flex justify-content-between align-items-center mb-2 text-xs">
                    <span class="text-muted">Landowner Harvest Share</span>
                    <span class="fw-bold text-dark">15% Fresh Yield</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2 text-xs">
                    <span class="text-muted">Water / Fertilizer Split</span>
                    <span class="fw-bold text-success">Gardener Self-Provided</span>
                </div>
                <div class="d-flex justify-content-between align-items-center text-xs">
                    <span class="text-muted">Soil Health Guarantee</span>
                    <span class="fw-bold text-dark">Organic Certified Only</span>
                </div>
            </div>
        </div>
    </div>
</main>
