document.addEventListener("DOMContentLoaded", function() {
    let requestsList = [];

    // DOM elements
    const requestsTableBody = document.getElementById('requestsTableBody');
    const viewResponseModalEl = document.getElementById('viewResponseModal');

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

    function renderRequests() {
        if (requestsList.length === 0) {
            requestsTableBody.innerHTML = `
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-send fs-1 d-block mb-2"></i>
                    <span>You haven't submitted any plot lease applications yet. Visit 'Search' to get started.</span>
                </div>
            `;
            return;
        }

        requestsTableBody.innerHTML = requestsList.map(req => {
            let statusBadge = '';
            if (req.status === 'approved') {
                statusBadge = `<span class="badge bg-success rounded-pill" style="font-size: 10px;">Approved</span>`;
            } else if (req.status === 'pending') {
                statusBadge = `<span class="badge bg-warning text-dark rounded-pill" style="font-size: 10px;">Pending</span>`;
            } else {
                statusBadge = `<span class="badge bg-danger rounded-pill" style="font-size: 10px;">Rejected</span>`;
            }

            return `
                <div class="d-flex align-items-center justify-content-between px-4 py-2 border-bottom hover:bg-[#f8faff] text-sm text-dark transition-colors" style="min-height: 58px;">
                    <!-- Land / Plot -->
                    <div class="w-50">
                        <div class="fw-semibold text-dark">${req.land_title}</div>
                        <span class="badge bg-light text-secondary border mt-1" style="font-size: 9px;">${req.plot_num}</span>
                    </div>

                    <!-- Lease Term -->
                    <div class="w-20 text-dark">${req.duration}</div>

                    <!-- Status -->
                    <div class="w-15">${statusBadge}</div>

                    <!-- Action -->
                    <div class="w-15 text-end">
                        <button onclick="openDetailsModal(${req.id})" class="btn icon-btn-pill">
                            <i class="bi bi-info-circle text-primary"></i>
                        </button>
                    </div>
                </div>
            `;
        }).join('');
    }

    window.openDetailsModal = function(id) {
        const req = requestsList.find(r => r.id === id);
        if (!req) return;

        document.getElementById('modal_land_title').innerText = req.land_title + ' — ' + req.plot_num;
        document.getElementById('modal_purpose').innerText = req.purpose;
        document.getElementById('modal_duration').innerText = req.duration;

        const feedbackSection = document.getElementById('modal_feedback_section');
        if (req.notes) {
            feedbackSection.classList.remove('hidden');
            document.getElementById('modal_feedback').innerText = req.notes;
        } else {
            feedbackSection.classList.add('hidden');
        }

        const viewModal = new bootstrap.Modal(viewResponseModalEl);
        viewModal.show();
    };

    loadRequests();
});
