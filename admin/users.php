<?php
$base_path = '../';
$page_title = "Manage Users";
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
?>

<main class="workspace-surface">
    <!-- Toolbar/Title Bar -->
    <div class="toolbar border-bottom">
        <div>
            <h1 class="fs-5 fw-semibold m-0 text-dark">Manage Users</h1>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-drive-primary d-flex align-items-center gap-2" data-bs-toggle="modal"
                data-bs-target="#addUserModal">
                <i class="bi bi-plus-lg"></i>
                <span>Add User</span>
            </button>
        </div>
    </div>

    <!-- Workspace Scrollable Area -->
    <div class="workspace-scroll">
        <!-- Search and Filter Bar inside Workspace -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-secondary rounded-pill px-3 filter-btn active"
                    data-filter="all">All</button>
                <button class="btn btn-sm btn-outline-secondary rounded-pill px-3 filter-btn"
                    data-filter="admin">Admins</button>
                <button class="btn btn-sm btn-outline-secondary rounded-pill px-3 filter-btn"
                    data-filter="landowner">Landowners</button>
                <button class="btn btn-sm btn-outline-secondary rounded-pill px-3 filter-btn"
                    data-filter="gardener">Gardeners</button>
            </div>
            <div class="text-muted" id="usersCountText" style="font-size: 0.8rem;">
                Showing <strong>0</strong> users
            </div>
        </div>

        <!-- Users Data Table (Google list layout style) -->
        <div class="border rounded-4 bg-white overflow-hidden  border-light-subtle">
            <!-- Header Row -->
            <div class="d-flex align-items-center justify-content-between px-4 py-2 bg-light border-bottom text-secondary"
                style="font-size: 0.75rem; font-weight: 600;">
                <div class="w-25">NAME</div>
                <div class="w-25">EMAIL</div>
                <div class="w-15">PHONE</div>
                <div class="w-10">ROLE</div>
                <div class="w-10">STATUS</div>
                <div class="w-15 text-end">ACTIONS</div>
            </div>

            <!-- User Rows Placeholder -->
            <div id="userTableBody"></div>
        </div>
    </div>
</main>

<!-- Global Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true"
    style="z-index: 9999;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content drive-modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold text-dark" id="addUserModalLabel">Add User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addUserForm">
                    <div class="mb-3">
                        <label for="add_name" class="form-label text-secondary"
                            style="font-size: 0.75rem; font-weight:600;">FULL NAME</label>
                        <input type="text" class="form-control drive-form-control w-100" id="add_name" name="name"
                            required placeholder="John Doe">
                    </div>
                    <div class="mb-3">
                        <label for="add_email" class="form-label text-secondary"
                            style="font-size: 0.75rem; font-weight:600;">EMAIL</label>
                        <input type="email" class="form-control drive-form-control w-100" id="add_email" name="email"
                            required placeholder="john@example.com">
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="add_role" class="form-label text-secondary"
                                style="font-size: 0.75rem; font-weight:600;">ROLE</label>
                            <select class="form-select drive-form-control w-100" id="add_role" name="role" required>
                                <option value="gardener">Gardener</option>
                                <option value="landowner">Landowner</option>
                                <option value="admin">Administrator</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="add_phone" class="form-label text-secondary"
                                style="font-size: 0.75rem; font-weight:600;">PHONE</label>
                            <input type="text" class="form-control drive-form-control w-100" id="add_phone" name="phone"
                                placeholder="09123456789">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="add_password" class="form-label text-secondary"
                            style="font-size: 0.75rem; font-weight:600;">PASSWORD</label>
                        <div class="input-group">
                            <input type="password" class="form-control drive-form-control" id="add_password"
                                name="password" required value="password123"
                                style="border-top-right-radius: 0; border-bottom-right-radius: 0; border-right: 0;">
                            <button class="btn btn-outline-secondary border-light-subtle" type="button"
                                id="toggleAddPassword"
                                style="border-top-right-radius: 8px; border-bottom-right-radius: 8px; border-color: var(--drive-border);">
                                <i class="bi bi-eye" id="toggleAddPasswordIcon"></i>
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

<!-- Global Edit User Modal (Single instance updated dynamically by JS) -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true"
    style="z-index: 9999;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content drive-modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold text-dark" id="editUserModalLabel">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editUserForm">
                    <input type="hidden" id="edit_user_id" name="id">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label text-secondary"
                            style="font-size: 0.75rem; font-weight:600;">FULL NAME</label>
                        <input type="text" class="form-control drive-form-control w-100" id="edit_name" name="name"
                            required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_email" class="form-label text-secondary"
                            style="font-size: 0.75rem; font-weight:600;">EMAIL</label>
                        <input type="email" class="form-control drive-form-control w-100" id="edit_email" name="email"
                            required>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit_role" class="form-label text-secondary"
                                style="font-size: 0.75rem; font-weight:600;">ROLE</label>
                            <select class="form-select drive-form-control w-100" id="edit_role" name="role" required>
                                <option value="gardener">Gardener</option>
                                <option value="landowner">Landowner</option>
                                <option value="admin">Administrator</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_phone" class="form-label text-secondary"
                                style="font-size: 0.75rem; font-weight:600;">PHONE</label>
                            <input type="text" class="form-control drive-form-control w-100" id="edit_phone"
                                name="phone">
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-drive-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-drive-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo $base_path; ?>assets/js/users.js"></script>

<?php include '../includes/footer.php'; ?>