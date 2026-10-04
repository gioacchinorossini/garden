/**
 * Landowner Requests Management JS
 * Handles responsive desktop table & mobile cards, filters, search, modals, and actions.
 */
document.addEventListener("DOMContentLoaded", function () {
    let requestsList = [];
    let currentFilter = 'all';
    let searchQuery = '';

    // DOM Elements - Desktop & Mobile
    const requestsTableBody = document.getElementById('requestsTableBody');
    const requestsMobileCards = document.getElementById('requestsMobileCards');
    const desktopSearch = document.getElementById('requestsSearchInput');
    const mobileSearch = document.getElementById('mobileSearchInput');

    // Count Badges
    const countAll = document.getElementById('countAll');
    const countPending = document.getElementById('countPending');
    const countApproved = document.getElementById('countApproved');
    const countRejected = document.getElementById('countRejected');
    const mCountAll = document.getElementById('mCountAll');
    const mCountPending = document.getElementById('mCountPending');
    const mCountApproved = document.getElementById('mCountApproved');
    const mCountRejected = document.getElementById('mCountRejected');

    // Modals
    const viewRequestModalEl = document.getElementById('viewRequestModal');
    const rejectRequestModalEl = document.getElementById('rejectRequestModal');
    const rejectForm = document.getElementById('rejectRequestForm');
    const requestToastEl = document.getElementById('requestToast');

    // Avatar Color Palette
    const AVATAR_COLORS = [
        { bg: '#dcfce7', text: '#15803d' }, // Emerald
        { bg: '#e0f2fe', text: '#0369a1' }, // Sky
        { bg: '#fef3c7', text: '#b45309' }, // Amber
        { bg: '#f3e8ff', text: '#7e22ce' }, // Purple
        { bg: '#ffe4e6', text: '#be123c' }, // Rose
        { bg: '#ccfbf1', text: '#0f766e' }, // Teal
    ];

    function getAvatarColor(name) {
        let hash = 0;
        for (let i = 0; i < (name || '').length; i++) {
            hash = name.charCodeAt(i) + ((hash << 5) - hash);
        }
        return AVATAR_COLORS[Math.abs(hash) % AVATAR_COLORS.length];
    }

    function getInitials(name) {
        if (!name) return 'GD';
        const parts = name.trim().split(/\s+/);
        if (parts.length >= 2) {
            return (parts[0][0] + parts[1][0]).toUpperCase();
        }
        return parts[0].substring(0, 2).toUpperCase();
    }

    function timeAgo(dateStr) {
        if (!dateStr) return '';
        const d = new Date(dateStr.replace(' ', 'T'));
        if (isNaN(d.getTime())) return dateStr;
        const now = new Date();
        const diffSec = Math.floor((now - d) / 1000);
        if (diffSec < 60) return 'Just now';
        const diffMin = Math.floor(diffSec / 60);
        if (diffMin < 60) return `${diffMin}m ago`;
        const diffHours = Math.floor(diffMin / 60);
        if (diffHours < 24) return `${diffHours}h ago`;
        const diffDays = Math.floor(diffHours / 24);
        if (diffDays === 1) return 'Yesterday';
        if (diffDays < 7) return `${diffDays}d ago`;
        return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    }

    function showToast(message, isSuccess = true) {
        if (!requestToastEl) return;
        const toastMsg = document.getElementById('toastMsg');
        const toastIcon = document.getElementById('toastIcon');
        if (toastMsg) toastMsg.textContent = message;
        if (toastIcon) {
            toastIcon.className = isSuccess 
                ? 'bi bi-check-circle-fill text-success fs-5'
                : 'bi bi-exclamation-triangle-fill text-danger fs-5';
        }
        const toast = new bootstrap.Toast(requestToastEl, { delay: 3500 });
        toast.show();
    }

    // Fetch requests from API
    async function loadRequests() {
        try {
            const response = await fetch('../api/requests.php');
            const res = await response.json();
            if (res.status === 'success') {
                requestsList = res.data.requests || [];
                updateCounts();
                renderRequests();
            } else {
                console.error("API error:", res.message);
            }
        } catch (err) {
            console.error("Failed to load requests:", err);
        }
    }

    // Update Counts on Badges
    function updateCounts() {
        const total = requestsList.length;
        const pending = requestsList.filter(r => r.status === 'pending').length;
        const approved = requestsList.filter(r => r.status === 'approved').length;
        const rejected = requestsList.filter(r => r.status === 'rejected').length;

        if (countAll) countAll.textContent = total;
        if (countPending) countPending.textContent = pending;
        if (countApproved) countApproved.textContent = approved;
        if (countRejected) countRejected.textContent = rejected;

        if (mCountAll) mCountAll.textContent = total;
        if (mCountPending) mCountPending.textContent = pending;
        if (mCountApproved) mCountApproved.textContent = approved;
        if (mCountRejected) mCountRejected.textContent = rejected;
    }

    // Filter and Search logic
    function getFilteredList() {
        return requestsList.filter(req => {
            // Status filter
            if (currentFilter !== 'all' && req.status !== currentFilter) {
                return false;
            }
            // Search filter
            if (searchQuery.trim() !== '') {
                const q = searchQuery.toLowerCase();
                const matchName = (req.gardener || '').toLowerCase().includes(q);
                const matchLand = (req.land_title || '').toLowerCase().includes(q);
                const matchPlot = (req.plot_num || '').toLowerCase().includes(q);
                const matchPurpose = (req.purpose || '').toLowerCase().includes(q);
                return matchName || matchLand || matchPlot || matchPurpose;
            }
            return true;
        });
    }

    // Render both desktop table and mobile cards
    function renderRequests() {
        const filtered = getFilteredList();

        // 1. Render Desktop Table Body
        if (requestsTableBody) {
            if (filtered.length === 0) {
                requestsTableBody.innerHTML = `
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                        <span class="fw-semibold">No requests found</span>
                        <p class="small text-muted mb-0 mt-1">There are no gardener applications matching the current criteria.</p>
                    </div>
                `;
            } else {
                requestsTableBody.innerHTML = filtered.map(req => {
                    const initials = getInitials(req.gardener);
                    const color = getAvatarColor(req.gardener);
                    const time = timeAgo(req.requested_at);

                    let statusBadge = '';
                    if (req.status === 'approved') {
                        statusBadge = `<span class="badge request-badge-approved rounded-pill px-2.5 py-1.5 fw-semibold d-inline-flex align-items-center gap-1"><i class="bi bi-check-circle-fill"></i> Approved</span>`;
                    } else if (req.status === 'pending') {
                        statusBadge = `<span class="badge request-badge-pending rounded-pill px-2.5 py-1.5 fw-semibold d-inline-flex align-items-center gap-1"><i class="bi bi-clock-history"></i> Pending</span>`;
                    } else {
                        statusBadge = `<span class="badge request-badge-rejected rounded-pill px-2.5 py-1.5 fw-semibold d-inline-flex align-items-center gap-1" title="Feedback: ${req.notes || ''}"><i class="bi bi-x-circle-fill"></i> Rejected</span>`;
                    }

                    // Desktop action buttons
                    let actionsHtml = `
                        <button onclick="openViewModal(${req.id})" class="request-action-btn request-btn-view" title="View application details">
                            <i class="bi bi-eye"></i>
                        </button>
                    `;

                    if (req.status === 'pending') {
                        actionsHtml += `
                            <button onclick="openRejectModal(${req.id})" class="request-action-btn request-btn-reject" title="Reject Request">
                                <i class="bi bi-x-lg"></i>
                            </button>
                            <button onclick="approveRequest(${req.id})" class="request-action-btn request-btn-approve" title="Approve Request">
                                <i class="bi bi-check-lg"></i>
                            </button>
                        `;
                    }

                    return `
                        <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom request-table-row">
                            <!-- Gardener -->
                            <div style="width: 28%;" class="d-flex align-items-center gap-2.5 min-w-0 pe-2">
                                <div class="request-avatar" style="background-color: ${color.bg}; color: ${color.text};">
                                    ${initials}
                                </div>
                                <div class="min-w-0">
                                    <div class="fw-bold text-dark text-truncate">${req.gardener}</div>
                                    <div class="text-muted small text-truncate" style="font-size: 11px;">
                                        ${req.email || req.phone || 'Gardener'}
                                    </div>
                                </div>
                            </div>

                            <!-- Land & Plot -->
                            <div style="width: 28%;" class="pe-2">
                                <div class="fw-semibold text-dark text-truncate">${req.land_title}</div>
                                <div class="d-flex align-items-center gap-1.5 mt-0.5">
                                    <span class="badge bg-light text-secondary border rounded-pill px-2 py-0.5" style="font-size: 10px;">
                                        <i class="bi bi-grid-3x3 text-success me-1"></i>${req.plot_num}
                                    </span>
                                    <span class="text-muted" style="font-size: 11px;">• ${time}</span>
                                </div>
                            </div>

                            <!-- Purpose / Term -->
                            <div style="width: 16%;" class="pe-2">
                                <span class="badge bg-light text-dark border rounded-pill px-2 py-1 mb-1 font-monospace" style="font-size: 10px;">
                                    ${req.duration}
                                </span>
                                <div class="text-muted text-truncate" style="font-size: 11px; max-width: 160px;" title="${req.purpose}">
                                    ${req.purpose}
                                </div>
                            </div>

                            <!-- Status -->
                            <div style="width: 14%;">
                                ${statusBadge}
                            </div>

                            <!-- Actions -->
                            <div style="width: 14%;" class="text-end d-flex align-items-center justify-content-end gap-1.5">
                                ${actionsHtml}
                            </div>
                        </div>
                    `;
                }).join('');
            }
        }

        // 2. Render Mobile Cards View
        if (requestsMobileCards) {
            if (filtered.length === 0) {
                requestsMobileCards.innerHTML = `
                    <div class="text-center py-5 text-muted bg-white rounded-4 border border-light-subtle p-4">
                        <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                        <span class="fw-semibold">No requests found</span>
                        <p class="small text-muted mb-0 mt-1">There are no gardener applications matching the current criteria.</p>
                    </div>
                `;
            } else {
                requestsMobileCards.innerHTML = filtered.map(req => {
                    const initials = getInitials(req.gardener);
                    const color = getAvatarColor(req.gardener);
                    const time = timeAgo(req.requested_at);

                    let statusBadge = '';
                    if (req.status === 'approved') {
                        statusBadge = `<span class="badge request-badge-approved rounded-pill px-2.5 py-1 text-xs fw-semibold"><i class="bi bi-check-circle-fill me-1"></i>Approved</span>`;
                    } else if (req.status === 'pending') {
                        statusBadge = `<span class="badge request-badge-pending rounded-pill px-2.5 py-1 text-xs fw-semibold"><i class="bi bi-clock-history me-1"></i>Pending</span>`;
                    } else {
                        statusBadge = `<span class="badge request-badge-rejected rounded-pill px-2.5 py-1 text-xs fw-semibold"><i class="bi bi-x-circle-fill me-1"></i>Rejected</span>`;
                    }

                    let mobileActions = `
                        <button onclick="openViewModal(${req.id})" class="btn btn-sm btn-light rounded-pill px-3 py-1.5 text-secondary flex-grow-1 fw-semibold" style="font-size: 0.8rem;">
                            <i class="bi bi-eye me-1"></i> Details
                        </button>
                    `;

                    if (req.status === 'pending') {
                        mobileActions = `
                            <button onclick="openRejectModal(${req.id})" class="btn btn-sm btn-danger rounded-pill px-3 py-1.5 flex-grow-1 fw-semibold text-white" style="font-size: 0.8rem;">
                                <i class="bi bi-x-lg me-1"></i> Reject
                            </button>
                            <button onclick="approveRequest(${req.id})" class="btn btn-sm btn-success rounded-pill px-3 py-1.5 text-white flex-grow-1 fw-semibold shadow-xs" style="font-size: 0.8rem;">
                                <i class="bi bi-check-lg me-1"></i> Approve
                            </button>
                            <button onclick="openViewModal(${req.id})" class="btn btn-sm btn-light rounded-pill px-2.5 py-1.5 text-secondary" title="Details">
                                <i class="bi bi-eye"></i>
                            </button>
                        `;
                    }

                    return `
                        <div class="request-mobile-card shadow-xs">
                            <!-- Card Header: Avatar, Name, Status -->
                            <div class="d-flex align-items-center justify-content-between mb-2.5">
                                <div class="d-flex align-items-center gap-2 min-w-0">
                                    <div class="request-avatar" style="background-color: ${color.bg}; color: ${color.text}; width: 34px; height: 34px; font-size: 0.78rem;">
                                        ${initials}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="fw-bold text-dark text-truncate" style="font-size: 0.9rem;">${req.gardener}</div>
                                        <div class="text-muted" style="font-size: 11px;">${time}</div>
                                    </div>
                                </div>
                                <div>
                                    ${statusBadge}
                                </div>
                            </div>

                            <!-- Land & Plot meta -->
                            <div class="p-2.5 bg-light rounded-3 mb-2.5 border border-light-subtle">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <strong class="text-dark small text-truncate"><i class="bi bi-geo-alt text-success me-1"></i>${req.land_title}</strong>
                                    <span class="badge bg-white text-success border rounded-pill px-2 py-0.5" style="font-size: 10px;">${req.plot_num}</span>
                                </div>
                                <div class="text-muted small mt-1" style="font-size: 0.78rem;">
                                    <i class="bi bi-calendar3 me-1"></i>Term: <strong class="text-dark">${req.duration}</strong>
                                </div>
                            </div>

                            <!-- Purpose Quote -->
                            <div class="mb-3 text-secondary" style="font-size: 0.82rem; line-height: 1.4;">
                                "${req.purpose}"
                            </div>

                            <!-- Footer Actions -->
                            <div class="d-flex align-items-center gap-2 pt-2 border-top">
                                ${mobileActions}
                            </div>
                        </div>
                    `;
                }).join('');
            }
        }
    }

    // Filter Buttons Event Delegation
    document.querySelectorAll('.request-filter-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const filter = this.getAttribute('data-filter') || 'all';
            currentFilter = filter;

            // Sync all filter buttons across desktop and mobile
            document.querySelectorAll('.request-filter-btn').forEach(b => {
                if (b.getAttribute('data-filter') === filter) {
                    b.classList.add('active');
                    b.classList.remove('text-secondary', 'bg-white');
                } else {
                    b.classList.remove('active');
                    b.classList.add('text-secondary');
                }
            });

            renderRequests();
        });
    });

    // Real-time Search Listeners
    if (desktopSearch) {
        desktopSearch.addEventListener('input', function () {
            searchQuery = this.value;
            if (mobileSearch) mobileSearch.value = searchQuery;
            renderRequests();
        });
    }

    if (mobileSearch) {
        mobileSearch.addEventListener('input', function () {
            searchQuery = this.value;
            if (desktopSearch) desktopSearch.value = searchQuery;
            renderRequests();
        });
    }

    // Approve Action
    window.approveRequest = async function (id) {
        const req = requestsList.find(r => r.id === id);
        const gardenerName = req ? req.gardener : 'this gardener';

        if (!confirm(`Are you sure you want to approve the plot application for ${gardenerName}?`)) return;

        try {
            const response = await fetch('../api/requests.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'update_status',
                    status: 'approved',
                    id: id,
                    notes: 'Application approved! Welcome to the garden community.'
                })
            });
            const res = await response.json();
            if (res.status === 'success') {
                // Close view modal if open
                const viewModal = bootstrap.Modal.getInstance(viewRequestModalEl);
                if (viewModal) viewModal.hide();

                showToast(`Plot application for ${gardenerName} approved successfully!`);
                await loadRequests();
            } else {
                showToast("Error: " + res.message, false);
            }
        } catch (err) {
            console.error("Approve request failed:", err);
            showToast("Failed to connect to the server.", false);
        }
    };

    // Open View Details Modal
    window.openViewModal = function (id) {
        const req = requestsList.find(r => r.id === id);
        if (!req) return;

        const initials = getInitials(req.gardener);
        const color = getAvatarColor(req.gardener);
        const time = timeAgo(req.requested_at);

        // Header
        const viewAvatar = document.getElementById('view_avatar');
        if (viewAvatar) {
            viewAvatar.innerText = initials;
            viewAvatar.style.backgroundColor = color.bg;
            viewAvatar.style.color = color.text;
        }

        const titleEl = document.getElementById('view_gardener_title');
        if (titleEl) titleEl.innerText = req.gardener;

        const dateEl = document.getElementById('view_date');
        if (dateEl) dateEl.innerText = `Requested ${time} (${req.requested_at})`;

        // Body
        document.getElementById('view_gardener').innerText = req.gardener;

        // Contact info with actionable links
        const contactEl = document.getElementById('view_contact');
        if (contactEl) {
            let emailHtml = req.email 
                ? `<a href="mailto:${req.email}" class="text-decoration-none text-dark hover:text-success"><i class="bi bi-envelope text-success me-1"></i>${req.email}</a>`
                : '';
            let phoneHtml = req.phone 
                ? `<a href="tel:${req.phone}" class="text-decoration-none text-dark hover:text-success"><i class="bi bi-telephone text-success me-1"></i>${req.phone}</a>`
                : '';
            contactEl.innerHTML = [emailHtml, phoneHtml].filter(Boolean).join(' • ');
        }

        // Status Pill
        const statusPillEl = document.getElementById('view_status_pill');
        if (statusPillEl) {
            if (req.status === 'approved') {
                statusPillEl.innerHTML = `<span class="badge request-badge-approved rounded-pill px-2.5 py-1 text-xs"><i class="bi bi-check-circle-fill me-1"></i>Approved</span>`;
            } else if (req.status === 'pending') {
                statusPillEl.innerHTML = `<span class="badge request-badge-pending rounded-pill px-2.5 py-1 text-xs"><i class="bi bi-clock-history me-1"></i>Pending</span>`;
            } else {
                statusPillEl.innerHTML = `<span class="badge request-badge-rejected rounded-pill px-2.5 py-1 text-xs"><i class="bi bi-x-circle-fill me-1"></i>Rejected</span>`;
            }
        }

        document.getElementById('view_plot').innerHTML = `<i class="bi bi-geo-alt text-success me-1"></i>${req.land_title} <span class="badge bg-white text-secondary border ms-1">${req.plot_num}</span>`;
        document.getElementById('view_duration').innerText = req.duration;
        document.getElementById('view_purpose').innerText = req.purpose || 'No description provided.';

        // Feedback section
        const feedbackBlock = document.getElementById('view_feedback_section');
        const feedbackContent = document.getElementById('view_feedback');
        if (req.notes) {
            feedbackBlock.classList.remove('d-none');
            feedbackContent.className = req.status === 'rejected'
                ? 'alert alert-danger py-2 px-3 mb-0'
                : 'alert alert-success py-2 px-3 mb-0';
            feedbackContent.innerHTML = `<strong>${req.status === 'rejected' ? 'Rejection Reason:' : 'Feedback:'}</strong> ${req.notes}`;
        } else {
            feedbackBlock.classList.add('d-none');
        }

        // In-modal pending actions
        const modalPendingActions = document.getElementById('modalPendingActions');
        if (modalPendingActions) {
            if (req.status === 'pending') {
                modalPendingActions.innerHTML = `
                    <button type="button" class="btn btn-danger rounded-pill px-3.5 py-1.5 text-white fw-semibold small" onclick="openRejectModal(${req.id})">
                        <i class="bi bi-x-lg me-1"></i> Reject
                    </button>
                    <button type="button" class="btn btn-success rounded-pill px-3.5 py-1.5 text-white fw-semibold small shadow-xs" onclick="approveRequest(${req.id})">
                        <i class="bi bi-check-lg me-1"></i> Approve
                    </button>
                `;
            } else {
                modalPendingActions.innerHTML = '';
            }
        }

        const modal = new bootstrap.Modal(viewRequestModalEl);
        modal.show();
    };

    // Open Reject Modal
    window.openRejectModal = function (id) {
        // Hide view modal if open
        const viewModal = bootstrap.Modal.getInstance(viewRequestModalEl);
        if (viewModal) viewModal.hide();

        const req = requestsList.find(r => r.id === id);
        const nameEl = document.getElementById('rejectGardenerName');
        if (nameEl) nameEl.textContent = req ? req.gardener : 'the gardener';

        document.getElementById('reject_request_id').value = id;
        document.getElementById('reject_notes').value = '';

        const modal = new bootstrap.Modal(rejectRequestModalEl);
        modal.show();
    };

    // Quick Reason Chips
    document.querySelectorAll('.quick-reason-chip').forEach(chip => {
        chip.addEventListener('click', function () {
            const reason = this.getAttribute('data-reason');
            const textarea = document.getElementById('reject_notes');
            if (textarea && reason) {
                textarea.value = reason;
                textarea.focus();
            }
        });
    });

    // Reject Form Submit
    if (rejectForm) {
        rejectForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            const id = parseInt(document.getElementById('reject_request_id').value);
            const notes = document.getElementById('reject_notes').value;

            try {
                const response = await fetch('../api/requests.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'update_status',
                        status: 'rejected',
                        id: id,
                        notes: notes
                    })
                });
                const res = await response.json();
                if (res.status === 'success') {
                    const modal = bootstrap.Modal.getInstance(rejectRequestModalEl);
                    if (modal) modal.hide();
                    rejectForm.reset();
                    showToast('Application rejected with feedback sent.');
                    await loadRequests();
                } else {
                    showToast("Error: " + res.message, false);
                }
            } catch (err) {
                console.error("Reject request failed:", err);
                showToast("Failed to connect to the server.", false);
            }
        });
    }

    // Initial load
    loadRequests();
});
