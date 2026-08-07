document.addEventListener("DOMContentLoaded", function() {
    let requestsList = [];
    let showPendingOnly = false;

    // DOM Elements
    const requestsTableBody = document.getElementById('requestsTableBody');
    const filterAllBtn = document.getElementById('filterAllBtn');
    const filterPendingBtn = document.getElementById('filterPendingBtn');

    // Forms and Modals
    const viewRequestModalEl = document.getElementById('viewRequestModal');
    const rejectRequestModalEl = document.getElementById('rejectRequestModal');
    const rejectForm = document.getElementById('rejectRequestForm');

    // Fetch requests
    async function loadRequests() {
        try {
            const response = await fetch('../api/requests.php');
            const res = await response.json();
            if (res.status === 'success') {
                requestsList = res.data.requests;
                renderRequests();
            } else {
                console.error("API error:", res.message);
            }
        } catch (err) {
            console.error("Failed to load requests:", err);
        }
    }

    // Render requests
    function renderRequests() {
        const filtered = requestsList.filter(req => {
            if (showPendingOnly) return req.status === 'pending';
            return true;
        });

        if (filtered.length === 0) {
            requestsTableBody.innerHTML = `
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-file-earmark-person fs-1 d-block mb-2"></i>
                    <span>No gardener requests found.</span>
                </div>
            `;
            return;
        }

        requestsTableBody.innerHTML = filtered.map(req => {
            let statusBadge = '';
            if (req.status === 'approved') {
                statusBadge = `<span class="badge bg-success rounded-pill" style="font-size: 10px;">Approved</span>`;
            } else if (req.status === 'pending') {
                statusBadge = `<span class="badge bg-warning text-dark rounded-pill" style="font-size: 10px;">Pending</span>`;
            } else {
                statusBadge = `<span class="badge bg-danger rounded-pill" style="font-size: 10px;" title="Feedback: ${req.notes}">Rejected</span>`;
            }

            // Actions block
            let actionsHtml = `
                <button onclick="openViewModal(${req.id})" class="btn icon-btn-pill" title="View details">
                    <i class="bi bi-eye text-primary"></i>
                </button>
            `;

            if (req.status === 'pending') {
                actionsHtml += `
                    <button onclick="approveRequest(${req.id})" class="btn icon-btn-pill" title="Approve Request">
                        <i class="bi bi-check-circle-fill text-success"></i>
                    </button>
                    <button onclick="openRejectModal(${req.id})" class="btn icon-btn-pill" title="Reject Request">
                        <i class="bi bi-x-circle-fill text-danger"></i>
                    </button>
                `;
            }

            return `
                <div class="d-flex align-items-center justify-content-between px-4 py-2 border-bottom hover:bg-[#f8faff] text-sm text-dark transition-colors" style="min-height: 58px;">
                    <!-- Gardener -->
                    <div class="w-35">
                         <div class="fw-semibold text-dark">${req.gardener}</div>
                         <small class="text-muted" style="font-size: 11px;">${req.email}</small>
                    </div>

                    <!-- Land / Plot -->
                    <div class="w-35">
                         <div class="fw-medium text-dark">${req.land_title}</div>
                         <span class="badge bg-light text-secondary border mt-1" style="font-size: 9px;">${req.plot_num}</span>
                    </div>

                    <!-- Lease Term -->
                    <div class="w-10 text-dark">${req.duration}</div>

                    <!-- Status -->
                    <div class="w-10">${statusBadge}</div>

                    <!-- Actions -->
                    <div class="w-10 text-end d-flex align-items-center justify-content-end gap-1">
                        ${actionsHtml}
                    </div>
                </div>
            `;
        }).join('');
    }

    // Toggle filters
    if (filterAllBtn && filterPendingBtn) {
        filterAllBtn.addEventListener('click', function() {
            filterAllBtn.classList.add('active');
            filterPendingBtn.classList.remove('active');
            showPendingOnly = false;
            renderRequests();
        });

        filterPendingBtn.addEventListener('click', function() {
            filterPendingBtn.classList.add('active');
            filterAllBtn.classList.remove('active');
            showPendingOnly = true;
            renderRequests();
        });
    }

    // Approve operation
    window.approveRequest = async function(id) {
        if (!confirm("Are you sure you want to approve this request?")) return;

        try {
            const response = await fetch('../api/requests.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'update_status',
                    status: 'approved',
                    id,
                    notes: 'Request approved! Looking forward to your harvest.'
                })
            });
            const res = await response.json();
            if (res.status === 'success') {
                loadRequests();
            } else {
                alert("Error: " + res.message);
            }
        } catch (err) {
            console.error("Approve request failed:", err);
        }
    };

    // Open View Modal
    window.openViewModal = function(id) {
        const req = requestsList.find(r => r.id === id);
        if (!req) return;

        document.getElementById('view_gardener').innerText = req.gardener;
        document.getElementById('view_contact').innerHTML = `<i class="bi bi-envelope"></i> ${req.email} • <i class="bi bi-telephone"></i> ${req.phone}`;
        document.getElementById('view_plot').innerHTML = `<strong>${req.land_title}</strong> (${req.plot_num})`;
        document.getElementById('view_purpose').innerText = req.purpose;
        document.getElementById('view_duration').innerText = req.duration;

        const feedbackBlock = document.getElementById('view_feedback_section');
        if (req.notes) {
            feedbackBlock.classList.remove('hidden');
            document.getElementById('view_feedback').innerText = req.notes;
        } else {
            feedbackBlock.classList.add('hidden');
        }

        const modal = new bootstrap.Modal(viewRequestModalEl);
        modal.show();
    };

    // Open Reject Modal
    window.openRejectModal = function(id) {
        document.getElementById('reject_request_id').value = id;
        document.getElementById('reject_notes').value = '';
        
        const modal = new bootstrap.Modal(rejectRequestModalEl);
        modal.show();
    };

    // Reject Form submit
    if (rejectForm) {
        rejectForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const payload = {
                action: 'update_status',
                status: 'rejected',
                id: parseInt(document.getElementById('reject_request_id').value),
                notes: document.getElementById('reject_notes').value
            };

            try {
                const response = await fetch('../api/requests.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const res = await response.json();
                if (res.status === 'success') {
                    const modal = bootstrap.Modal.getInstance(rejectRequestModalEl);
                    if (modal) modal.hide();
                    rejectForm.reset();
                    loadRequests();
                } else {
                    alert("Error: " + res.message);
                }
            } catch (err) {
                console.error("Reject request failed:", err);
            }
        });
    }

    // Initial load
    loadRequests();
});
