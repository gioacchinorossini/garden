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
    const countOffer = document.getElementById('countOffer');
    const countApproved = document.getElementById('countApproved');
    const countRejected = document.getElementById('countRejected');
    const mCountAll = document.getElementById('mCountAll');
    const mCountPending = document.getElementById('mCountPending');
    const mCountOffer = document.getElementById('mCountOffer');
    const mCountApproved = document.getElementById('mCountApproved');
    const mCountRejected = document.getElementById('mCountRejected');

    // Modals
    const viewRequestModalEl = document.getElementById('viewRequestModal');
    const rejectRequestModalEl = document.getElementById('rejectRequestModal');
    const sendRentalOfferModalEl = document.getElementById('sendRentalOfferModal');
    const rejectForm = document.getElementById('rejectRequestForm');
    const sendOfferForm = document.getElementById('sendRentalOfferForm');
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
        const offers = requestsList.filter(r => r.status === 'offer_sent').length;
        const counterOffered = requestsList.filter(r => r.status === 'counter_offered').length;
        const approved = requestsList.filter(r => r.status === 'approved').length;
        const rejected = requestsList.filter(r => r.status === 'rejected' || r.status === 'declined').length;

        const countCounter = document.getElementById('countCounter');
        const mCountCounter = document.getElementById('mCountCounter');

        if (countAll) countAll.textContent = total;
        if (countPending) countPending.textContent = pending;
        if (countOffer) countOffer.textContent = offers;
        if (countCounter) countCounter.textContent = counterOffered;
        if (countApproved) countApproved.textContent = approved;
        if (countRejected) countRejected.textContent = rejected;

        if (mCountAll) mCountAll.textContent = total;
        if (mCountPending) mCountPending.textContent = pending;
        if (mCountOffer) mCountOffer.textContent = offers;
        if (mCountCounter) mCountCounter.textContent = counterOffered;
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
                    } else if (req.status === 'offer_sent') {
                        const rateStr = req.offered_rental_rate ? `₱${parseFloat(req.offered_rental_rate).toLocaleString()}/yr` : '₱110,000/yr';
                        statusBadge = `<span class="badge rounded-pill px-2.5 py-1.5 fw-semibold d-inline-flex align-items-center gap-1" style="background:#FEF3C7;color:#92400E;border:1px solid #FCD34D;" title="Offer ${rateStr} sent to renter"><i class="bi bi-tag-fill text-warning"></i> Offer Sent</span>`;
                    } else if (req.status === 'counter_offered') {
                        const counterRateStr = req.counter_rental_rate ? `₱${parseFloat(req.counter_rental_rate).toLocaleString()}/yr` : '₱.../yr';
                        statusBadge = `<span class="badge rounded-pill px-2.5 py-1.5 fw-semibold d-inline-flex align-items-center gap-1" style="background:#E0F2FE;color:#0369A1;border:1px solid #BAE6FD;" title="Counter-Offer: ${counterRateStr}"><i class="bi bi-arrow-left-right text-primary"></i> Counter: ${counterRateStr}</span>`;
                    } else if (req.status === 'declined') {
                        statusBadge = `<span class="badge rounded-pill px-2.5 py-1.5 fw-semibold d-inline-flex align-items-center gap-1 bg-danger-subtle text-danger border border-danger-subtle"><i class="bi bi-x-circle-fill"></i> Declined</span>`;
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
                            <button onclick="openRentalOfferModal(${req.id})" class="btn btn-sm btn-outline-success rounded-pill px-2.5 py-1 text-xs fw-semibold d-inline-flex align-items-center gap-1 ms-1 shadow-xs" title="Send Rental Offer">
                                <i class="bi bi-tag-fill"></i> Offer
                            </button>
                            <button onclick="openRejectModal(${req.id})" class="request-action-btn request-btn-reject ms-1" title="Reject Request">
                                <i class="bi bi-x-lg"></i>
                            </button>
                            <button onclick="approveRequest(${req.id})" class="request-action-btn request-btn-approve ms-1" title="Direct Approve">
                                <i class="bi bi-check-lg"></i>
                            </button>
                        `;
                    } else if (req.status === 'offer_sent') {
                        actionsHtml += `
                            <span class="text-[11px] text-muted font-medium ms-1 text-nowrap d-inline-flex align-items-center gap-1" title="Awaiting gardener decision">
                                <i class="bi bi-hourglass-split text-warning"></i> Awaiting Renter
                            </span>
                        `;
                    } else if (req.status === 'counter_offered') {
                        const counterRateStr = req.counter_rental_rate ? `₱${parseFloat(req.counter_rental_rate).toLocaleString()}` : '';
                        actionsHtml += `
                            <button onclick="acceptCounterOffer(${req.id})" class="btn btn-sm btn-success rounded-pill px-2.5 py-1 text-xs fw-semibold text-white d-inline-flex align-items-center gap-1 ms-1 shadow-xs" title="Accept Counter-Offer & Lease Plot">
                                <i class="bi bi-check-circle-fill"></i> Accept
                            </button>
                            <button onclick="openRentalOfferModal(${req.id})" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-1 text-xs fw-semibold ms-1" title="Counter Back / Propose Adjusted Rate">
                                <i class="bi bi-arrow-left-right"></i> Counter
                            </button>
                            <button onclick="declineCounterOffer(${req.id})" class="request-action-btn request-btn-reject ms-1" title="Decline Counter-Offer">
                                <i class="bi bi-x-lg"></i>
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
                    } else if (req.status === 'offer_sent') {
                        const rateStr = req.offered_rental_rate ? `₱${parseFloat(req.offered_rental_rate).toLocaleString()}/yr` : '₱110,000/yr';
                        statusBadge = `<span class="badge rounded-pill px-2.5 py-1 text-xs fw-semibold" style="background:#FEF3C7;color:#92400E;border:1px solid #FCD34D;" title="Offer ${rateStr} sent"><i class="bi bi-tag-fill me-1 text-warning"></i>Offer Sent</span>`;
                    } else if (req.status === 'counter_offered') {
                        const counterRateStr = req.counter_rental_rate ? `₱${parseFloat(req.counter_rental_rate).toLocaleString()}/yr` : '₱.../yr';
                        statusBadge = `<span class="badge rounded-pill px-2.5 py-1 text-xs fw-semibold" style="background:#E0F2FE;color:#0369A1;border:1px solid #BAE6FD;"><i class="bi bi-arrow-left-right me-1 text-primary"></i>Counter: ${counterRateStr}</span>`;
                    } else if (req.status === 'declined') {
                        statusBadge = `<span class="badge rounded-pill px-2.5 py-1 text-xs fw-semibold bg-danger-subtle text-danger border border-danger-subtle"><i class="bi bi-x-circle-fill me-1"></i>Declined</span>`;
                    } else {
                        statusBadge = `<span class="badge request-badge-rejected rounded-pill px-2.5 py-1 text-xs fw-semibold"><i class="bi bi-x-circle-fill me-1"></i>Rejected</span>`;
                    }

                    let mobileActions = '';
                    if (req.status === 'pending') {
                        mobileActions = `
                            <div class="d-flex align-items-center gap-1.5 pt-2 border-top w-100 flex-wrap" onclick="event.stopPropagation()">
                                <button onclick="openRentalOfferModal(${req.id})" class="btn btn-sm btn-outline-success rounded-pill px-2.5 py-1.5 flex-grow-1 fw-semibold shadow-xs" style="font-size: 0.78rem;">
                                    <i class="bi bi-tag-fill me-1"></i> Offer
                                </button>
                                <button onclick="openRejectModal(${req.id})" class="btn btn-sm btn-outline-danger rounded-pill px-2.5 py-1.5 fw-semibold" style="font-size: 0.78rem;">
                                    <i class="bi bi-x-lg me-1"></i> Reject
                                </button>
                                <button onclick="approveRequest(${req.id})" class="btn btn-sm btn-success rounded-pill px-2.5 py-1.5 text-white fw-semibold shadow-xs" style="font-size: 0.78rem;">
                                    <i class="bi bi-check-lg me-1"></i> Approve
                                </button>
                            </div>
                        `;
                    } else if (req.status === 'offer_sent') {
                        const rateStr = req.offered_rental_rate ? `₱${parseFloat(req.offered_rental_rate).toLocaleString()}/yr` : '₱110,000/yr';
                        mobileActions = `
                            <div class="pt-2 border-top w-100 text-center" onclick="event.stopPropagation()">
                                <span class="badge bg-light text-secondary border rounded-pill px-3 py-1.5 text-xs fw-medium d-inline-flex align-items-center gap-1.5">
                                    <i class="bi bi-hourglass-split text-warning"></i> Offer Sent (${rateStr}) • Awaiting Renter
                                </span>
                            </div>
                        `;
                    } else if (req.status === 'counter_offered') {
                        const counterRateStr = req.counter_rental_rate ? `₱${parseFloat(req.counter_rental_rate).toLocaleString()}/yr` : '';
                        mobileActions = `
                            <div class="d-flex align-items-center gap-1.5 pt-2 border-top w-100 flex-wrap" onclick="event.stopPropagation()">
                                <button onclick="acceptCounterOffer(${req.id})" class="btn btn-sm btn-success rounded-pill px-2.5 py-1.5 flex-grow-1 text-white fw-semibold shadow-xs" style="font-size: 0.78rem;">
                                    <i class="bi bi-check-circle-fill me-1"></i> Accept (${counterRateStr})
                                </button>
                                <button onclick="openRentalOfferModal(${req.id})" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-1.5 fw-semibold" style="font-size: 0.78rem;">
                                    <i class="bi bi-arrow-left-right me-1"></i> Counter Back
                                </button>
                                <button onclick="declineCounterOffer(${req.id})" class="btn btn-sm btn-outline-danger rounded-pill px-2.5 py-1.5 fw-semibold" style="font-size: 0.78rem;">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        `;
                    }

                    return `
                        <div class="request-mobile-card shadow-xs" onclick="openViewModal(${req.id})" role="button" tabindex="0" style="cursor: pointer;">
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
                                <div class="d-flex align-items-center gap-1.5">
                                    ${statusBadge}
                                    <i class="bi bi-chevron-right text-muted opacity-50 ms-1" style="font-size: 0.75rem;"></i>
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
                            <div class="mb-2 text-secondary" style="font-size: 0.82rem; line-height: 1.4;">
                                "${req.purpose}"
                            </div>

                            <!-- Footer Actions (if pending or offer_sent) -->
                            ${mobileActions}
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
            } else if (req.status === 'offer_sent') {
                const rateStr = req.offered_rental_rate ? `₱${parseFloat(req.offered_rental_rate).toLocaleString()}/yr` : '₱110,000/yr';
                statusPillEl.innerHTML = `<span class="badge rounded-pill px-2.5 py-1 text-xs fw-semibold" style="background:#FEF3C7;color:#92400E;border:1px solid #FCD34D;"><i class="bi bi-tag-fill me-1 text-warning"></i>Offer Sent (${rateStr})</span>`;
            } else if (req.status === 'counter_offered') {
                const counterRateStr = req.counter_rental_rate ? `₱${parseFloat(req.counter_rental_rate).toLocaleString()}/yr` : '₱.../yr';
                statusPillEl.innerHTML = `<span class="badge rounded-pill px-2.5 py-1 text-xs fw-semibold" style="background:#E0F2FE;color:#0369A1;border:1px solid #BAE6FD;"><i class="bi bi-arrow-left-right me-1 text-primary"></i>Counter-Offer Received (${counterRateStr})</span>`;
            } else if (req.status === 'declined') {
                statusPillEl.innerHTML = `<span class="badge rounded-pill px-2.5 py-1 text-xs fw-semibold bg-danger-subtle text-danger border border-danger-subtle"><i class="bi bi-x-circle-fill me-1"></i>Declined by Gardener</span>`;
            } else {
                statusPillEl.innerHTML = `<span class="badge request-badge-rejected rounded-pill px-2.5 py-1 text-xs"><i class="bi bi-x-circle-fill me-1"></i>Rejected</span>`;
            }
        }

        document.getElementById('view_plot').innerHTML = `<i class="bi bi-geo-alt text-success me-1"></i>${req.land_title} <span class="badge bg-white text-secondary border ms-1">${req.plot_num}</span>`;
        document.getElementById('view_duration').innerText = req.duration;
        document.getElementById('view_purpose').innerText = req.purpose || 'No description provided.';

        // Gardener Counter-Offer section
        const counterSection = document.getElementById('view_counter_offer_section');
        if (counterSection) {
            if (req.status === 'counter_offered') {
                counterSection.classList.remove('d-none');
                const cRate = req.counter_rental_rate ? parseFloat(req.counter_rental_rate) : 0;
                const cRateEl = document.getElementById('view_counter_rate');
                const cMonthlyEl = document.getElementById('view_counter_monthly');
                const cNotesEl = document.getElementById('view_counter_notes');
                const cTimeEl = document.getElementById('view_counter_time');

                if (cRateEl) cRateEl.innerText = `₱${cRate.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
                if (cMonthlyEl) cMonthlyEl.innerText = `(₱${(cRate / 12).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}/mo)`;
                if (cNotesEl) cNotesEl.innerText = req.counter_notes ? `"${escapeHtml(req.counter_notes)}"` : 'No additional notes provided.';
                if (cTimeEl) cTimeEl.innerText = req.counter_at ? timeAgo(req.counter_at) : '';
            } else {
                counterSection.classList.add('d-none');
            }
        }

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

        // Land Location Map inside Details
        const mapSection = document.getElementById('view_map_section');
        const lat = parseFloat(req.latitude);
        const lng = parseFloat(req.longitude);
        const hasCoords = !isNaN(lat) && !isNaN(lng) && (lat !== 0 || lng !== 0);

        if (mapSection) {
            if (hasCoords || req.land_address) {
                mapSection.classList.remove('d-none');
                const coordsEl = document.getElementById('view_map_coords');
                if (coordsEl) coordsEl.innerText = hasCoords ? `${lat.toFixed(5)}, ${lng.toFixed(5)}` : (req.land_address || '');

                const gmapsLink = document.getElementById('view_gmaps_link');
                if (gmapsLink) {
                    gmapsLink.href = hasCoords
                        ? `https://www.google.com/maps?q=${lat},${lng}`
                        : `https://www.google.com/maps/search/${encodeURIComponent(req.land_title + ' ' + (req.land_address || ''))}`;
                }

                const mapEl = document.getElementById('viewLeafletMap');
                if (hasCoords && mapEl) {
                    mapEl.parentElement.classList.remove('d-none');
                    renderLandownerModalMap(lat, lng, req.land_title, req.plot_num);
                } else if (mapEl) {
                    mapEl.parentElement.classList.add('d-none');
                }
            } else {
                mapSection.classList.add('d-none');
            }
        }

        // In-modal pending actions
        const modalPendingActions = document.getElementById('modalPendingActions');
        if (modalPendingActions) {
            if (req.status === 'pending') {
                modalPendingActions.innerHTML = `
                    <button type="button" class="btn btn-outline-success rounded-pill px-3.5 py-1.5 fw-semibold small shadow-xs" onclick="openRentalOfferModal(${req.id})">
                        <i class="bi bi-tag-fill me-1"></i> Send Rental Offer
                    </button>
                    <button type="button" class="btn btn-outline-danger rounded-pill px-3.5 py-1.5 fw-semibold small" onclick="openRejectModal(${req.id})">
                        <i class="bi bi-x-lg me-1"></i> Reject
                    </button>
                    <button type="button" class="btn btn-success rounded-pill px-3.5 py-1.5 text-white fw-semibold small shadow-xs" onclick="approveRequest(${req.id})">
                        <i class="bi bi-check-lg me-1"></i> Direct Approve
                    </button>
                `;
            } else if (req.status === 'offer_sent') {
                const rateStr = req.offered_rental_rate ? `₱${parseFloat(req.offered_rental_rate).toLocaleString()}/yr` : '₱110,000/yr';
                modalPendingActions.innerHTML = `
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-3 py-1.5 small d-inline-flex align-items-center gap-1.5">
                        <i class="bi bi-hourglass-split"></i> Rental Offer Sent: ${rateStr} (${req.rental_duration || '1 Year'}) • Waiting for Gardener Decision
                    </span>
                `;
            } else if (req.status === 'counter_offered') {
                const counterRate = req.counter_rental_rate ? parseFloat(req.counter_rental_rate) : 0;
                modalPendingActions.innerHTML = `
                    <button type="button" class="btn btn-success rounded-pill px-3.5 py-1.5 text-white fw-semibold small shadow-xs" onclick="acceptCounterOffer(${req.id})">
                        <i class="bi bi-check-circle-fill me-1"></i> Accept Counter-Offer (₱${counterRate.toLocaleString()}/yr)
                    </button>
                    <button type="button" class="btn btn-outline-primary rounded-pill px-3 py-1.5 fw-semibold small" onclick="openRentalOfferModal(${req.id})">
                        <i class="bi bi-arrow-left-right me-1"></i> Propose New Offer
                    </button>
                    <button type="button" class="btn btn-outline-danger rounded-pill px-3 py-1.5 fw-semibold small" onclick="declineCounterOffer(${req.id})">
                        <i class="bi bi-x-lg me-1"></i> Decline
                    </button>
                `;
            } else {
                modalPendingActions.innerHTML = '';
            }
        }

        const modal = new bootstrap.Modal(viewRequestModalEl);
        modal.show();
    };

    // Accept Gardener Counter-Offer
    window.acceptCounterOffer = async function (id) {
        const req = requestsList.find(r => r.id === id);
        const counterRate = req && req.counter_rental_rate ? parseFloat(req.counter_rental_rate) : 0;
        const gardenerName = req ? req.gardener : 'the gardener';
        const plotNum = req ? req.plot_num : 'this plot';

        const confirmMsg = `Do you want to accept ${gardenerName}'s counter-offer of ₱${counterRate.toLocaleString('en-US', {minimumFractionDigits: 2})}/year for ${plotNum}?\n\nAccepting this counter-offer confirms the lease agreement, sets the annual rental rate, and marks the plot as Occupied.`;
        if (!confirm(confirmMsg)) return;

        try {
            const response = await fetch('../api/requests.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'accept_counter_offer',
                    id: id
                })
            });
            const res = await response.json();
            if (res.status === 'success') {
                const viewModal = bootstrap.Modal.getInstance(viewRequestModalEl);
                if (viewModal) viewModal.hide();

                showToast(res.message || 'Counter-offer accepted! Lease agreement is now active.');
                await loadRequests();
            } else {
                showToast("Error: " + res.message, false);
            }
        } catch (err) {
            console.error("Accept counter-offer failed:", err);
            showToast("Failed to connect to the server.", false);
        }
    };

    // Decline Gardener Counter-Offer
    window.declineCounterOffer = async function (id) {
        if (!confirm("Are you sure you want to decline this gardener counter-offer?")) return;

        try {
            const response = await fetch('../api/requests.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'update_status',
                    status: 'declined',
                    id: id,
                    notes: 'Counter-offer was declined by the landowner.'
                })
            });
            const res = await response.json();
            if (res.status === 'success') {
                const viewModal = bootstrap.Modal.getInstance(viewRequestModalEl);
                if (viewModal) viewModal.hide();

                showToast('Counter-offer declined.');
                await loadRequests();
            } else {
                showToast("Error: " + res.message, false);
            }
        } catch (err) {
            console.error("Decline counter-offer failed:", err);
            showToast("Failed to connect to the server.", false);
        }
    };

    let _landownerModalMap = null;

    function renderLandownerModalMap(lat, lng, title, plotNum) {
        const initMap = () => {
            const mapEl = document.getElementById('viewLeafletMap');
            if (!mapEl || typeof L === 'undefined') return;

            if (_landownerModalMap) {
                _landownerModalMap.remove();
                _landownerModalMap = null;
            }

            _landownerModalMap = L.map(mapEl, {
                center: [lat, lng],
                zoom: 16,
                zoomControl: true,
                scrollWheelZoom: false
            });

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap'
            }).addTo(_landownerModalMap);

            L.circle([lat, lng], {
                color: '#2D5A27',
                fillColor: '#7FA668',
                fillOpacity: 0.18,
                weight: 2,
                radius: 40
            }).addTo(_landownerModalMap);

            const pinIcon = L.divIcon({
                className: '',
                html: `<div style="width:28px;height:28px;background:#2D5A27;border-radius:50% 50% 50% 0;transform:rotate(-45deg);border:2.5px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,0.25);"></div>`,
                iconSize: [28, 28],
                iconAnchor: [14, 28]
            });

            L.marker([lat, lng], { icon: pinIcon })
                .bindPopup(`<strong>${title}</strong><br>${plotNum}`)
                .addTo(_landownerModalMap)
                .openPopup();

            setTimeout(() => {
                if (_landownerModalMap) {
                    _landownerModalMap.invalidateSize();
                }
            }, 120);
        };

        if (viewRequestModalEl && viewRequestModalEl.classList.contains('show')) {
            setTimeout(initMap, 80);
        } else if (viewRequestModalEl) {
            const onShown = function () {
                viewRequestModalEl.removeEventListener('shown.bs.modal', onShown);
                initMap();
            };
            viewRequestModalEl.addEventListener('shown.bs.modal', onShown);
        }
    }

    if (viewRequestModalEl) {
        viewRequestModalEl.addEventListener('hidden.bs.modal', function () {
            if (_landownerModalMap) {
                _landownerModalMap.remove();
                _landownerModalMap = null;
            }
        });
    }

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

    // Open Rental Offer Modal
    window.openRentalOfferModal = function (id) {
        // Hide view modal if open
        const viewModal = bootstrap.Modal.getInstance(viewRequestModalEl);
        if (viewModal) viewModal.hide();

        const req = requestsList.find(r => r.id === id);
        if (!req) return;

        const idInput = document.getElementById('offer_request_id');
        const nameEl = document.getElementById('offer_gardener_name');
        const descEl = document.getElementById('offer_plot_desc');
        const rateInput = document.getElementById('offer_rental_rate');
        const durationSelect = document.getElementById('offer_rental_duration');
        const termsInput = document.getElementById('offer_terms');

        if (idInput) idInput.value = id;
        if (nameEl) nameEl.textContent = req.gardener || 'Gardener';
        if (descEl) descEl.innerHTML = `<i class="bi bi-geo-alt text-success me-1"></i>${req.land_title} • <strong>${req.plot_num}</strong>`;

        // Default rental price: benchmark is ₱110,000/yr for 50 sq. m.
        const defaultRate = req.counter_rental_rate || req.offered_rental_rate || req.rental_price_per_year || 110000;
        if (rateInput) rateInput.value = parseFloat(defaultRate);

        if (durationSelect && req.duration) {
            // Pick duration if matches
            for (let opt of durationSelect.options) {
                if (opt.value.toLowerCase().includes(req.duration.toLowerCase()) || req.duration.toLowerCase().includes(opt.value.toLowerCase())) {
                    durationSelect.value = opt.value;
                    break;
                }
            }
        }

        if (termsInput && req.terms_conditions) {
            termsInput.value = req.terms_conditions;
        }

        const modal = new bootstrap.Modal(sendRentalOfferModalEl);
        modal.show();
    };

    // Send Rental Offer Form Submit
    if (sendOfferForm) {
        sendOfferForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            const id = parseInt(document.getElementById('offer_request_id').value);
            const rentalRate = parseFloat(document.getElementById('offer_rental_rate').value) || 110000;
            const duration = document.getElementById('offer_rental_duration').value || '1 Year';
            const terms = document.getElementById('offer_terms').value || '';

            try {
                const response = await fetch('../api/requests.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'make_rental_offer',
                        id: id,
                        offered_rental_rate: rentalRate,
                        rental_duration: duration,
                        terms_conditions: terms
                    })
                });
                const res = await response.json();
                if (res.status === 'success') {
                    const modal = bootstrap.Modal.getInstance(sendRentalOfferModalEl);
                    if (modal) modal.hide();
                    sendOfferForm.reset();
                    showToast(`Rental offer of ₱${rentalRate.toLocaleString()}/yr sent to gardener!`);
                    await loadRequests();
                } else {
                    showToast("Error: " + res.message, false);
                }
            } catch (err) {
                console.error("Send rental offer failed:", err);
                showToast("Failed to connect to the server.", false);
            }
        });
    }

    // Initial load
    loadRequests();
});

