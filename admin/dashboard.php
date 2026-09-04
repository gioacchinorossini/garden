<?php
$base_path = '../';
$page_title = "Admin Dashboard";
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
?>

<!-- Main Content Workspace Container -->
<main class="workspace-surface">
    <!-- Toolbar/Title Bar -->
    <div class="toolbar border-bottom">
        <div>
            <h1 class="fs-5 fw-semibold m-0 text-dark">Dashboard</h1>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-outline-secondary rounded-pill btn-sm px-3 d-flex align-items-center gap-1">
                <i data-lucide="calendar" style="width: 16px; height: 16px;"></i>
                <span>Last 30 Days</span>
            </button>
        </div>
    </div>

    <!-- Workspace Scrollable Area -->
    <div class="workspace-scroll">
        <!-- 1. Key Metrics Cards Grid -->
        <h2 class="fs-6 fw-semibold text-secondary mb-3" style="letter-spacing: 0.5px; text-transform: uppercase;">
            Overview</h2>
        <div class="row g-3 mb-4">
            <!-- Metric 1: Total Lands -->
            <div class="col-md-3">
                <div class="drive-card d-flex align-items-center gap-3 p-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center"
                        style="width: 52px; height: 52px; min-width: 52px; background-color: #e8f0fe; color: #1a73e8;">
                        <i data-lucide="map-pin" style="width: 28px; height: 28px;"></i>
                    </div>
                    <div>
                        <span class="text-secondary fw-semibold d-block"
                            style="font-size: 0.75rem; line-height: 1.2;">Registered Lands</span>
                        <h3 class="fw-bold m-0 mt-1 text-dark" style="font-size: 1.5rem;">12</h3>
                        <p class="text-muted mb-0 mt-0.5" style="font-size: 0.7rem;"><span
                                class="fw-bold d-inline-flex align-items-center gap-1" style="color: #1a73e8;"><i data-lucide="arrow-up" style="width: 12px; height: 12px;"></i> +2</span> this week</p>
                    </div>
                </div>
            </div>
            <!-- Metric 2: Active Gardeners -->
            <div class="col-md-3">
                <div class="drive-card d-flex align-items-center gap-3 p-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center"
                        style="width: 52px; height: 52px; min-width: 52px; background-color: #f3e5f5; color: #8e24aa;">
                        <i data-lucide="users" style="width: 28px; height: 28px;"></i>
                    </div>
                    <div>
                        <span class="text-secondary fw-semibold d-block"
                            style="font-size: 0.75rem; line-height: 1.2;">Active Gardeners</span>
                        <h3 class="fw-bold m-0 mt-1 text-dark" style="font-size: 1.5rem;">45</h3>
                        <p class="text-muted mb-0 mt-0.5" style="font-size: 0.7rem;"><span
                                class="fw-bold d-inline-flex align-items-center gap-1" style="color: #8e24aa;"><i data-lucide="arrow-up" style="width: 12px; height: 12px;"></i> +5</span> this month</p>
                    </div>
                </div>
            </div>
            <!-- Metric 3: Pending Approvals -->
            <div class="col-md-3">
                <div class="drive-card d-flex align-items-center gap-3 p-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center bg-warning-subtle text-warning-emphasis"
                        style="width: 52px; height: 52px; min-width: 52px;">
                        <i data-lucide="hourglass" style="width: 28px; height: 28px;"></i>
                    </div>
                    <div>
                        <span class="text-secondary fw-semibold d-block"
                            style="font-size: 0.75rem; line-height: 1.2;">Pending Lands</span>
                        <h3 class="fw-bold m-0 mt-1 text-warning" style="font-size: 1.5rem;">3</h3>
                        <p class="text-muted mb-0 mt-0.5" style="font-size: 0.7rem;">Needs review</p>
                    </div>
                </div>
            </div>
            <!-- Metric 4: Total Harvests (KG) -->
            <div class="col-md-3">
                <div class="drive-card d-flex align-items-center gap-3 p-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center bg-danger-subtle text-danger"
                        style="width: 52px; height: 52px; min-width: 52px;">
                        <i data-lucide="shopping-bag" style="width: 28px; height: 28px;"></i>
                    </div>
                    <div>
                        <span class="text-secondary fw-semibold d-block"
                            style="font-size: 0.75rem; line-height: 1.2;">Total Harvest</span>
                        <h3 class="fw-bold m-0 mt-1 text-danger" style="font-size: 1.5rem;">340 <span
                                style="font-size: 0.9rem;">kg</span></h3>
                        <p class="text-muted mb-0 mt-0.5" style="font-size: 0.7rem;"><span
                                class="text-danger fw-bold d-inline-flex align-items-center gap-1"><i data-lucide="arrow-up" style="width: 12px; height: 12px;"></i> +12%</span> vs last month
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Recent Land Registrations Panel (Mocking file-grid design from design.md) -->
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h2 class="fs-6 fw-semibold text-secondary m-0" style="letter-spacing: 0.5px; text-transform: uppercase;">
                Recent Submissions</h2>
            <a href="lands.php" class="text-decoration-none text-primary d-inline-flex align-items-center gap-1"
                style="font-size: 0.8rem; font-weight: 500;">View All Lands <i data-lucide="arrow-right" style="width: 14px; height: 14px;"></i></a>
        </div>

        <div class="row g-3 mb-4">
            <!-- Card 1 -->
            <div class="col-md-4">
                <div class="drive-card">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <i data-lucide="image" class="text-danger" style="width: 20px; height: 20px;"></i>
                            <span class="text-sm fw-semibold text-dark truncate" style="max-width: 160px;">Sunnyvale
                                Lot.jpg</span>
                        </div>
                        <span class="badge bg-warning text-dark rounded-pill" style="font-size: 10px;">Pending
                            Review</span>
                    </div>
                    <div class="drive-card-thumbnail">
                        <!-- Standard SVG placeholder image to guarantee loading without external internet dependency -->
                        <svg width="100%" height="100%" viewBox="0 0 100 60" style="background:#e9f2ff">
                            <rect width="100%" height="100%" fill="#eef3fa" />
                            <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle"
                                font-family="sans-serif" font-size="6" fill="#8cb3e3">Sunnyvale Gardening Space</text>
                            <circle cx="50%" cy="20%" r="5" fill="#8cb3e3" />
                        </svg>
                    </div>
                    <div class="mt-3">
                        <p class="mb-0 text-dark fw-medium" style="font-size: 0.85rem;">Sunnyvale Plots</p>
                        <p class="text-muted mb-0 d-flex align-items-center gap-1" style="font-size: 0.75rem;"><i data-lucide="map-pin" style="width: 12px; height: 12px;"></i>
                            124 Green Ave, Sunnyvale</p>
                    </div>
                </div>
            </div>
            <!-- Card 2 -->
            <div class="col-md-4">
                <div class="drive-card">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <i data-lucide="image" class="text-danger" style="width: 20px; height: 20px;"></i>
                            <span class="text-sm fw-semibold text-dark truncate" style="max-width: 160px;">Downtown
                                Roof.jpg</span>
                        </div>
                        <span class="badge rounded-pill" style="font-size: 10px; background-color: #e8f0fe; color: #1a73e8;">Approved</span>
                    </div>
                    <div class="drive-card-thumbnail">
                        <svg width="100%" height="100%" viewBox="0 0 100 60" style="background:#e9f2ff">
                            <rect width="100%" height="100%" fill="#eef3fa" />
                            <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle"
                                font-family="sans-serif" font-size="6" fill="#8cb3e3">Downtown Rooftop Plot</text>
                        </svg>
                    </div>
                    <div class="mt-3">
                        <p class="mb-0 text-dark fw-medium" style="font-size: 0.85rem;">Downtown Green Roof</p>
                        <p class="text-muted mb-0 d-flex align-items-center gap-1" style="font-size: 0.75rem;"><i data-lucide="map-pin" style="width: 12px; height: 12px;"></i>
                            45 Main St, Business District</p>
                    </div>
                </div>
            </div>
            <!-- Card 3 -->
            <div class="col-md-4">
                <div class="drive-card">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <i data-lucide="image" class="text-danger" style="width: 20px; height: 20px;"></i>
                            <span class="text-sm fw-semibold text-dark truncate" style="max-width: 160px;">Riverdale
                                Acres.jpg</span>
                        </div>
                        <span class="badge rounded-pill" style="font-size: 10px; background-color: #e8f0fe; color: #1a73e8;">Approved</span>
                    </div>
                    <div class="drive-card-thumbnail">
                        <svg width="100%" height="100%" viewBox="0 0 100 60" style="background:#e9f2ff">
                            <rect width="100%" height="100%" fill="#eef3fa" />
                            <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle"
                                font-family="sans-serif" font-size="6" fill="#8cb3e3">Riverdale Community Soil</text>
                        </svg>
                    </div>
                    <div class="mt-3">
                        <p class="mb-0 text-dark fw-medium" style="font-size: 0.85rem;">Riverdale Acres</p>
                        <p class="text-muted mb-0 d-flex align-items-center gap-1" style="font-size: 0.75rem;"><i data-lucide="map-pin" style="width: 12px; height: 12px;"></i>
                            Riverside Dr, Block B</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Recent Activity Log (Mocking File List Rows from design.md) -->
        <h2 class="fs-6 fw-semibold text-secondary mb-3" style="letter-spacing: 0.5px; text-transform: uppercase;">
            Recent Activity</h2>
        <div class="border rounded-4 bg-white overflow-hidden  mb-2"
            style="border-color: var(--drive-border) !important;">
            <!-- Table Header Row -->
            <div class="d-flex align-items-center justify-content-between h-10 px-4 border-b bg-light text-secondary"
                style="font-size: 0.75rem; font-weight: 600; height: 36px;">
                <div class="flex-1">ACTIVITY</div>
                <div class="w-36 text-start">USER</div>
                <div class="w-36 text-start">TIME</div>
                <div class="w-24 text-start">IP</div>
            </div>

            <!-- Row 1 -->
            <div class="drive-list-row">
                <div class="flex-1 d-flex align-items-center gap-3">
                    <i data-lucide="user-plus" style="color: #8e24aa; width: 20px; height: 20px;"></i>
                    <span>Registered new Gardener account <strong>Mary Gardener</strong></span>
                </div>
                <div class="w-36 text-secondary" style="font-size: 0.75rem;">System Registrar</div>
                <div class="w-36 text-secondary" style="font-size: 0.75rem;">Today, 07:15 AM</div>
                <div class="w-24 text-secondary" style="font-size: 0.75rem;">192.168.1.12</div>
            </div>
            <!-- Row 2 -->
            <div class="drive-list-row">
                <div class="flex-1 d-flex align-items-center gap-3">
                    <i data-lucide="file-check" style="color: #1a73e8; width: 20px; height: 20px;"></i>
                    <span>Approved Land Registration: <strong>Riverdale Acres</strong></span>
                </div>
                <div class="w-36 text-secondary" style="font-size: 0.75rem;">System Administrator</div>
                <div class="w-36 text-secondary" style="font-size: 0.75rem;">Yesterday, 04:30 PM</div>
                <div class="w-24 text-secondary" style="font-size: 0.75rem;">192.168.1.2</div>
            </div>
            <!-- Row 3 -->
            <div class="drive-list-row">
                <div class="flex-1 d-flex align-items-center gap-3">
                    <i data-lucide="trash-2" class="text-danger" style="width: 20px; height: 20px;"></i>
                    <span>Deleted inactive Landowner account ID: 104</span>
                </div>
                <div class="w-36 text-secondary" style="font-size: 0.75rem;">System Administrator</div>
                <div class="w-36 text-secondary" style="font-size: 0.75rem;">Aug 05, 2026, 11:20 AM</div>
                <div class="w-24 text-secondary" style="font-size: 0.75rem;">192.168.1.2</div>
            </div>
        </div>
    </div>
</main>

<!-- Add User Modal (Admin + New Trigger) -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content drive-modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold text-dark" id="addUserModalLabel">Create New User Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="users.php" method="POST">
                    <input type="hidden" name="action" value="create_user">
                    <div class="mb-3">
                        <label for="name" class="form-label text-secondary"
                            style="font-size: 0.75rem; font-weight:600;">FULL NAME</label>
                        <input type="text" class="form-control drive-form-control w-100" id="name" name="name" required
                            placeholder="e.g. John Doe">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label text-secondary"
                            style="font-size: 0.75rem; font-weight:600;">EMAIL ADDRESS</label>
                        <input type="email" class="form-control drive-form-control w-100" id="email" name="email"
                            required placeholder="e.g. john@example.com">
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="role" class="form-label text-secondary"
                                style="font-size: 0.75rem; font-weight:600;">SYSTEM ROLE</label>
                            <select class="form-select drive-form-control w-100" id="role" name="role" required>
                                <option value="gardener">Gardener</option>
                                <option value="landowner">Landowner</option>
                                <option value="admin">Administrator</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label text-secondary"
                                style="font-size: 0.75rem; font-weight:600;">PHONE NUMBER</label>
                            <input type="text" class="form-control drive-form-control w-100" id="phone" name="phone"
                                placeholder="e.g. 09123456789">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label text-secondary"
                            style="font-size: 0.75rem; font-weight:600;">DEFAULT PASSWORD</label>
                        <div class="input-group">
                            <input type="password" class="form-control drive-form-control" id="password" name="password"
                                required value="password123"
                                style="border-top-right-radius: 0; border-bottom-right-radius: 0; border-right: 0;">
                            <button class="btn btn-outline-secondary border-light-subtle" type="button"
                                id="toggleDashboardPassword"
                                style="border-top-right-radius: 8px; border-bottom-right-radius: 8px; border-color: var(--drive-border);">
                                <i data-lucide="eye" id="toggleDashboardPasswordIcon" style="width: 16px; height: 16px;"></i>
                            </button>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-drive-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-drive-primary">Create User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const toggleDashboardPassword = document.getElementById('toggleDashboardPassword');
        const dashboardPasswordInput = document.getElementById('password');
        const toggleDashboardPasswordIcon = document.getElementById('toggleDashboardPasswordIcon');

        if (toggleDashboardPassword && dashboardPasswordInput && toggleDashboardPasswordIcon) {
            toggleDashboardPassword.addEventListener('click', function () {
                if (dashboardPasswordInput.type === 'password') {
                    dashboardPasswordInput.type = 'text';
                    toggleDashboardPasswordIcon.classList.remove('bi-eye');
                    toggleDashboardPasswordIcon.classList.add('bi-eye-slash');
                } else {
                    dashboardPasswordInput.type = 'password';
                    toggleDashboardPasswordIcon.classList.remove('bi-eye-slash');
                    toggleDashboardPasswordIcon.classList.add('bi-eye');
                }
            });
        }
    });
</script>

<?php include '../includes/footer.php'; ?>