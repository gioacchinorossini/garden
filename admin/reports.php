<?php
$base_path = '../';
$page_title = "System Reports";
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
?>

<main class="workspace-surface">
    <!-- Toolbar/Title Bar -->
    <div class="toolbar border-bottom">
        <div>
            <h1 class="fs-5 fw-semibold m-0 text-dark">Reports</h1>
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
            <!-- Metric Card 1: Land Utilization -->
            <div class="col-md-4">
                <div class="drive-card">
                    <span class="text-secondary fw-semibold" style="font-size: 0.75rem; letter-spacing:0.5px;">LAND UTILIZATION</span>
                    <div class="d-flex align-items-center justify-content-between mt-2">
                        <h3 class="fw-bold m-0" style="color: var(--drive-primary);">78.3%</h3>
                        <span class="text-success" style="font-size: 0.8rem; font-weight:600;"><i
                                class="bi bi-caret-up-fill"></i> +4.2%</span>
                    </div>
                    <div class="w-full bg-[#e1e3e1] h-2 rounded-full overflow-hidden mt-3 mb-1"
                        style="height: 6px; background-color: var(--drive-border);">
                        <div class="bg-primary h-full"
                            style="width: 78.3%; height:100%; background-color: var(--drive-primary) !important;"></div>
                    </div>
                    <small class="text-muted" style="font-size: 0.7rem;">650 m² / 830 m² occupied</small>
                </div>
            </div>

            <!-- Metric Card 2: Active plots -->
            <div class="col-md-4">
                <div class="drive-card">
                    <span class="text-secondary fw-semibold" style="font-size: 0.75rem; letter-spacing:0.5px;">ACTIVE PLOTS</span>
                    <div class="d-flex align-items-center justify-content-between mt-2">
                        <h3 class="fw-bold m-0" style="color: var(--drive-text-main);">18 / 24</h3>
                        <span class="text-muted" style="font-size: 0.8rem;">6 available</span>
                    </div>
                    <div class="w-full bg-[#e1e3e1] h-2 rounded-full overflow-hidden mt-3 mb-1"
                        style="height: 6px; background-color: var(--drive-border);">
                        <div class="bg-success h-full" style="width: 75%; height:100%;"></div>
                    </div>
                    <small class="text-muted" style="font-size: 0.7rem;">75% occupied</small>
                </div>
            </div>

            <!-- Metric Card 3: Gardener productivity -->
            <div class="col-md-4">
                <div class="drive-card">
                    <span class="text-secondary fw-semibold" style="font-size: 0.75rem; letter-spacing:0.5px;">AVG YIELD / GARDENER</span>
                    <div class="d-flex align-items-center justify-content-between mt-2">
                        <h3 class="fw-bold m-0 text-success">18.9 kg</h3>
                        <span class="text-success" style="font-size: 0.8rem; font-weight:600;"><i
                                class="bi bi-caret-up-fill"></i> +1.1 kg</span>
                    </div>
                    <div class="w-full bg-[#e1e3e1] h-2 rounded-full overflow-hidden mt-3 mb-1"
                        style="height: 6px; background-color: var(--drive-border);">
                        <div class="bg-success h-full" style="width: 82%; height:100%;"></div>
                    </div>
                    <small class="text-muted" style="font-size: 0.7rem;">18 active gardeners</small>
                </div>
            </div>
        </div>

        <!-- 2. Yield Details (Bar graphs made of clean HTML & Tailwind styled bootstrap components) -->
        <div class="row g-4 mb-4">
            <!-- Left Graph: Crop Distribution -->
            <div class="col-md-6">
                <div class="card border rounded-4  p-4" style="border-color: var(--drive-border) !important;">
                    <h2 class="fs-6 fw-semibold text-secondary mb-3"
                        style="letter-spacing: 0.5px; text-transform: uppercase;">Harvest by Crop</h2>

                    <!-- Crop Item 1 -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1 align-items-center" style="font-size: 0.8rem;">
                            <span class="d-flex align-items-center gap-1.5"><img src="../assets/crop-icons/tomato/tomato.svg" style="width: 18px; height: 18px;" alt="Tomatoes"> Tomatoes</span>
                            <strong>140 kg (41.2%)</strong>
                        </div>
                        <div class="progress" style="height: 12px; border-radius: 50rem;">
                            <div class="progress-bar bg-danger" role="progressbar"
                                style="width: 41.2%; border-radius: 50rem;" aria-valuenow="41.2" aria-valuemin="0"
                                aria-valuemax="100"></div>
                        </div>
                    </div>

                    <!-- Crop Item 2 -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1 align-items-center" style="font-size: 0.8rem;">
                            <span class="d-flex align-items-center gap-1.5"><img src="../assets/crop-icons/romaine/romaine.svg" style="width: 18px; height: 18px;" alt="Lettuce"> Lettuce & Greens</span>
                            <strong>95 kg (27.9%)</strong>
                        </div>
                        <div class="progress" style="height: 12px; border-radius: 50rem;">
                            <div class="progress-bar bg-success" role="progressbar"
                                style="width: 27.9%; border-radius: 50rem;" aria-valuenow="27.9" aria-valuemin="0"
                                aria-valuemax="100"></div>
                        </div>
                    </div>

                    <!-- Crop Item 3 -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1 align-items-center" style="font-size: 0.8rem;">
                            <span class="d-flex align-items-center gap-1.5"><img src="../assets/crop-icons/carrot/carrot.svg" style="width: 18px; height: 18px;" alt="Carrots"> Carrots</span>
                            <strong>65 kg (19.1%)</strong>
                        </div>
                        <div class="progress" style="height: 12px; border-radius: 50rem;">
                            <div class="progress-bar bg-warning" role="progressbar"
                                style="width: 19.1%; border-radius: 50rem;" aria-valuenow="19.1" aria-valuemin="0"
                                aria-valuemax="100"></div>
                        </div>
                    </div>

                    <!-- Crop Item 4 -->
                    <div class="mb-2">
                        <div class="d-flex justify-content-between mb-1 align-items-center" style="font-size: 0.8rem;">
                            <span class="d-flex align-items-center gap-1.5"><img src="../assets/crop-icons/russet-potato/russet-potato.svg" style="width: 18px; height: 18px;" alt="Potatoes"> Others (Potatoes, Herbs)</span>
                            <strong>40 kg (11.8%)</strong>
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
                <div class="card border rounded-4  p-4 h-100" style="border-color: var(--drive-border) !important;">
                    <h2 class="fs-6 fw-semibold text-secondary mb-3"
                        style="letter-spacing: 0.5px; text-transform: uppercase;">Milestones</h2>

                    <div class="position-relative ps-4 border-start"
                        style="border-color: var(--drive-border) !important; font-size: 0.825rem;">
                        <!-- Timeline Item 1 -->
                        <div class="mb-3 position-relative">
                            <span class="position-absolute bg-primary rounded-circle"
                                style="width: 10px; height: 10px; left: -29px; top: 5px;"></span>
                            <strong class="text-dark d-block">Peak Harvest Season</strong>
                            <span class="text-muted d-block" style="font-size:0.75rem;">Aug 2026 • 150 kg produced</span>
                        </div>
                        <!-- Timeline Item 2 -->
                        <div class="mb-3 position-relative">
                            <span class="position-absolute bg-success rounded-circle"
                                style="width: 10px; height: 10px; left: -29px; top: 5px;"></span>
                            <strong class="text-dark d-block">Riverdale Acres Activated</strong>
                            <span class="text-muted d-block" style="font-size:0.75rem;">Jul 2026 • 10 plots allocated</span>
                        </div>
                        <!-- Timeline Item 3 -->
                        <div class="position-relative">
                            <span class="position-absolute bg-secondary rounded-circle"
                                style="width: 10px; height: 10px; left: -29px; top: 5px;"></span>
                            <strong class="text-dark d-block">Platform Launch</strong>
                            <span class="text-muted d-block" style="font-size:0.75rem;">Jun 2026 • System launched</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Top Harvest Logs Table -->
        <h2 class="fs-6 fw-semibold text-secondary mb-3" style="letter-spacing: 0.5px; text-transform: uppercase;">Top Harvests</h2>
        <div class="border rounded-4 bg-white overflow-hidden  border-light-subtle">
            <!-- Header Row -->
            <div class="d-flex align-items-center justify-content-between px-4 py-2 bg-light border-bottom text-secondary"
                style="font-size: 0.75rem; font-weight: 600;">
                <div class="w-25">CROP</div>
                <div class="w-25">GARDENER</div>
                <div class="w-25">LAND</div>
                <div class="w-15">YIELD</div>
                <div class="w-10 text-end">DATE</div>
            </div>

            <!-- Row 1 -->
            <div class="d-flex align-items-center justify-content-between px-4 py-2 border-bottom hover:bg-[#f8faff] text-sm text-dark transition-colors"
                style="height: 48px;">
                <div class="w-25 d-flex align-items-center gap-2 fw-semibold">
                    <img src="../assets/crop-icons/tomato/tomato.svg" style="width: 20px; height: 20px;" alt="Tomatoes"> Tomatoes
                </div>
                <div class="w-25 text-secondary">Mary Gardener</div>
                <div class="w-25 text-secondary">Downtown Rooftop Garden</div>
                <div class="w-15 fw-bold text-success">35.0 kg</div>
                <div class="w-10 text-end text-muted" style="font-size: 0.75rem;">Aug 04, 2026</div>
            </div>

            <!-- Row 2 -->
            <div class="d-flex align-items-center justify-content-between px-4 py-2 border-bottom hover:bg-[#f8faff] text-sm text-dark transition-colors"
                style="height: 48px;">
                <div class="w-25 d-flex align-items-center gap-2 fw-semibold">
                    <img src="../assets/crop-icons/romaine/romaine.svg" style="width: 20px; height: 20px;" alt="Romaine Lettuce"> Romaine Lettuce
                </div>
                <div class="w-25 text-secondary">Mary Gardener</div>
                <div class="w-25 text-secondary">Riverdale Acres</div>
                <div class="w-15 fw-bold text-success">22.5 kg</div>
                <div class="w-10 text-end text-muted" style="font-size: 0.75rem;">Aug 02, 2026</div>
            </div>

            <!-- Row 3 -->
            <div class="d-flex align-items-center justify-content-between px-4 py-2 border-bottom hover:bg-[#f8faff] text-sm text-dark transition-colors"
                style="height: 48px;">
                <div class="w-25 d-flex align-items-center gap-2 fw-semibold">
                    <i class="bi bi-egg text-warning"></i> Carrots
                </div>
                <div class="w-25 text-secondary">David Miller</div>
                <div class="w-25 text-secondary">Downtown Rooftop Garden</div>
                <div class="w-15 fw-bold text-success">18.0 kg</div>
                <div class="w-10 text-end text-muted" style="font-size: 0.75rem;">Jul 28, 2026</div>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>