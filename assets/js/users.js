document.addEventListener("DOMContentLoaded", function() {
    let usersList = [];
    let currentFilter = 'all'; // 'all', 'admin', 'landowner', 'gardener'

    // DOM Elements
    const userTableBody = document.getElementById('userTableBody');
    const usersCountText = document.getElementById('usersCountText');
    const filterButtons = document.querySelectorAll('.filter-btn');
    
    // Forms
    const addUserForm = document.getElementById('addUserForm');
    const editUserForm = document.getElementById('editUserForm');

    // Modals
    const addUserModalEl = document.getElementById('addUserModal');
    const editUserModalEl = document.getElementById('editUserModal');
    
    // Fetch users
    async function loadUsers() {
        try {
            const response = await fetch('../api/users.php');
            const res = await response.json();
            if (res.status === 'success') {
                usersList = res.data.users;
                renderUsers();
            } else {
                console.error("API error:", res.message);
            }
        } catch (err) {
            console.error("Failed to load users:", err);
        }
    }

    // Render users
    function renderUsers() {
        // Filter list
        const filtered = usersList.filter(user => {
            if (currentFilter === 'all') return true;
            return user.role === currentFilter;
        });

        // Update count
        if (usersCountText) {
            usersCountText.innerHTML = `Showing <strong>${filtered.length}</strong> users`;
        }

        if (filtered.length === 0) {
            userTableBody.innerHTML = `
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-people fs-1 d-block mb-2"></i>
                    <span>No users found.</span>
                </div>
            `;
            return;
        }

        userTableBody.innerHTML = filtered.map(user => {
            const initial = user.name ? user.name.charAt(0).toUpperCase() : '?';
            
            // Badge style for role
            let roleBadge = '';
            if (user.role === 'admin') {
                roleBadge = `<span class="badge bg-danger-subtle text-danger rounded-pill px-2 py-1" style="font-size: 10px;">Admin</span>`;
            } else if (user.role === 'landowner') {
                roleBadge = `<span class="badge bg-info-subtle text-info-emphasis rounded-pill px-2 py-1" style="font-size: 10px;">Landowner</span>`;
            } else {
                roleBadge = `<span class="badge bg-success-subtle text-success rounded-pill px-2 py-1" style="font-size: 10px;">Gardener</span>`;
            }

            // Badge style for status
            const statusBadge = user.status === 'active'
                ? `<span class="badge bg-success rounded-pill" style="font-size: 10px;">Active</span>`
                : `<span class="badge bg-secondary rounded-pill" style="font-size: 10px;">Inactive</span>`;

            return `
                <div class="d-flex align-items-center justify-content-between px-4 py-2 border-bottom hover:bg-[#f8faff] text-sm text-dark transition-colors" style="height: 54px;">
                    <!-- Name & Icon -->
                    <div class="w-25 d-flex align-items-center gap-2">
                        <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-weight: 600;">
                            ${initial}
                        </div>
                        <span class="fw-semibold text-dark">${user.name}</span>
                    </div>
                    
                    <!-- Email -->
                    <div class="w-25 text-secondary truncate">${user.email}</div>
                    
                    <!-- Phone -->
                    <div class="w-15 text-secondary">${user.phone || 'N/A'}</div>
                    
                    <!-- Role -->
                    <div class="w-10">${roleBadge}</div>
                    
                    <!-- Status -->
                    <div class="w-10">${statusBadge}</div>
                    
                    <!-- Actions -->
                    <div class="w-15 text-end d-flex align-items-center justify-content-end gap-1">
                        <!-- Toggle Status -->
                        <button onclick="toggleUserStatus(${user.id})" class="btn icon-btn-pill" title="Toggle active status">
                            <i class="bi bi-arrow-repeat text-warning"></i>
                        </button>
                        <!-- Edit -->
                        <button onclick="openEditModal(${user.id})" class="btn icon-btn-pill" title="Edit Account">
                            <i class="bi bi-pencil-square text-primary"></i>
                        </button>
                        <!-- Delete -->
                        <button onclick="deleteUser(${user.id})" class="btn icon-btn-pill" title="Delete Account">
                            <i class="bi bi-trash3 text-danger"></i>
                        </button>
                    </div>
                </div>
            `;
        }).join('');
    }

    // Filter Buttons logic
    filterButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            filterButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentFilter = this.getAttribute('data-filter');
            renderUsers();
        });
    });

    // Toggle Status operation
    window.toggleUserStatus = async function(id) {
        try {
            const response = await fetch('../api/users.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'toggle_status', id })
            });
            const res = await response.json();
            if (res.status === 'success') {
                loadUsers();
            } else {
                alert("Error: " + res.message);
            }
        } catch (err) {
            console.error("Toggle status failed:", err);
        }
    };

    // Open Edit Modal
    window.openEditModal = function(id) {
        const user = usersList.find(u => u.id === id);
        if (!user) return;

        document.getElementById('edit_user_id').value = user.id;
        document.getElementById('edit_name').value = user.name;
        document.getElementById('edit_email').value = user.email;
        document.getElementById('edit_role').value = user.role;
        document.getElementById('edit_phone').value = user.phone || '';

        const editModal = new bootstrap.Modal(editUserModalEl);
        editModal.show();
    };

    // Delete operation
    window.deleteUser = async function(id) {
        if (!confirm("Are you sure you want to delete this account?")) return;

        try {
            const response = await fetch('../api/users.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'delete_user', id })
            });
            const res = await response.json();
            if (res.status === 'success') {
                loadUsers();
            } else {
                alert("Error: " + res.message);
            }
        } catch (err) {
            console.error("Delete failed:", err);
        }
    };

    // Submit Add User Form
    if (addUserForm) {
        addUserForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const payload = {
                action: 'create_user',
                name: document.getElementById('add_name').value,
                email: document.getElementById('add_email').value,
                role: document.getElementById('add_role').value,
                phone: document.getElementById('add_phone').value
            };

            try {
                const response = await fetch('../api/users.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const res = await response.json();
                if (res.status === 'success') {
                    const modal = bootstrap.Modal.getInstance(addUserModalEl);
                    if (modal) modal.hide();
                    addUserForm.reset();
                    loadUsers();
                } else {
                    alert("Error: " + res.message);
                }
            } catch (err) {
                console.error("Create user failed:", err);
            }
        });
    }

    // Submit Edit User Form
    if (editUserForm) {
        editUserForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const payload = {
                action: 'update_user',
                id: parseInt(document.getElementById('edit_user_id').value),
                name: document.getElementById('edit_name').value,
                email: document.getElementById('edit_email').value,
                role: document.getElementById('edit_role').value,
                phone: document.getElementById('edit_phone').value
            };

            try {
                const response = await fetch('../api/users.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const res = await response.json();
                if (res.status === 'success') {
                    const modal = bootstrap.Modal.getInstance(editUserModalEl);
                    if (modal) modal.hide();
                    editUserForm.reset();
                    loadUsers();
                } else {
                    alert("Error: " + res.message);
                }
            } catch (err) {
                console.error("Update user failed:", err);
            }
        });
    }

    // Toggle Add User Password Visibility
    const toggleAddPassword = document.getElementById('toggleAddPassword');
    const addPasswordInput = document.getElementById('add_password');
    const toggleAddPasswordIcon = document.getElementById('toggleAddPasswordIcon');

    if (toggleAddPassword && addPasswordInput && toggleAddPasswordIcon) {
        toggleAddPassword.addEventListener('click', function() {
            if (addPasswordInput.type === 'password') {
                addPasswordInput.type = 'text';
                toggleAddPasswordIcon.classList.remove('bi-eye');
                toggleAddPasswordIcon.classList.add('bi-eye-slash');
            } else {
                addPasswordInput.type = 'password';
                toggleAddPasswordIcon.classList.remove('bi-eye-slash');
                toggleAddPasswordIcon.classList.add('bi-eye');
            }
        });
    }

    // Initial load
    loadUsers();
});
