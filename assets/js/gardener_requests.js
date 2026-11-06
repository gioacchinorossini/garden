/**
 * Gardener Requests Management JS
 * Handles responsive desktop table & mobile cards, filters, search, modals, and actions.
 * Matches Landowner Requests UI architecture and styling.
 */

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

document.addEventListener("DOMContentLoaded", function () {
    let requestsList = [];
    let currentFilter = 'all';
    let searchQuery = '';

    // DOM Elements - Desktop & Mobile
    const requestsTableBody = document.getElementById('requestsTableBody');
    const requestsMobileCards = document.getElementById('requestsMobileCards');
    const desktopSearch = document.getElementById('requestsSearchInput');
    const mobileSearch = document.getElementById('mobileSearchInput') || document.getElementById('mobileRequestsSearchInput');

    // Count Badges - Desktop
    const countAll = document.getElementById('countAll') || document.getElementById('filterCountAll');
    const countPending = document.getElementById('countPending') || document.getElementById('filterCountPending');
    const countOffer = document.getElementById('countOffer') || document.getElementById('filterCountOffer');
    const countCounter = document.getElementById('countCounter') || document.getElementById('filterCountCounter');
    const countApproved = document.getElementById('countApproved') || document.getElementById('filterCountApproved');
    const countRejected = document.getElementById('countRejected') || document.getElementById('filterCountRejected');

    // Count Badges - Mobile
    const mCountAll = document.getElementById('mCountAll') || document.getElementById('mFilterCountAll');
    const mCountPending = document.getElementById('mCountPending') || document.getElementById('mFilterCountPending');
    const mCountOffer = document.getElementById('mCountOffer') || document.getElementById('mFilterCountOffer');
    const mCountCounter = document.getElementById('mCountCounter') || document.getElementById('mFilterCountCounter');
    const mCountApproved = document.getElementById('mCountApproved') || document.getElementById('mFilterCountApproved');
    const mCountRejected = document.getElementById('mCountRejected') || document.getElementById('mFilterCountRejected');

    // Modals & Feedback
    const viewResponseModalEl = document.getElementById('viewResponseModal');
    const requestToastEl = document.getElementById('requestToast');

    // Land Icon SVG generator
    function getLandIconSvg() {
        return `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="40" height="40" class="gardener-request-icon-img" aria-label="Land Plot">
  <defs>
    <linearGradient id="landSkyGrad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#EBF5E9" />
      <stop offset="100%" stop-color="#D5ECC2" />
    </linearGradient>
    <linearGradient id="landFieldGrad1" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#7FA668" />
      <stop offset="100%" stop-color="#558B2F" />
    </linearGradient>
    <linearGradient id="landFieldGrad2" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#A4CE84" />
      <stop offset="100%" stop-color="#689F38" />
    </linearGradient>
  </defs>
  <rect x="2" y="2" width="44" height="44" rx="14" fill="url(#landSkyGrad)" stroke="#BDDAB8" stroke-width="1.5" />
  <circle cx="34" cy="15" r="5" fill="#FEE7AA" stroke="#E5A93C" stroke-width="1.2" opacity="0.9" />
  <path d="M2 30 C 12 24, 22 26, 32 23 C 38 21, 42 22, 46 25 L 46 44 C 46 45, 45 46, 44 46 L 4 46 C 3 46, 2 45, 2 44 Z" fill="url(#landFieldGrad2)" opacity="0.75"/>
  <path d="M2 34 C 10 30, 20 31, 30 28 C 38 26, 42 27, 46 29 L 46 44 C 46 45, 45 46, 44 46 L 4 46 C 3 46, 2 45, 2 44 Z" fill="url(#landFieldGrad1)" />
  <path d="M8 38 C 16 35, 26 36, 38 33" stroke="#2D5A27" stroke-width="1.8" stroke-linecap="round" fill="none" opacity="0.6"/>
  <path d="M6 42 C 15 39, 25 40, 40 37" stroke="#1E3E1A" stroke-width="1.8" stroke-linecap="round" fill="none" opacity="0.5"/>
  <path d="M19 33 L 19 20" stroke="#1E3E1A" stroke-width="2.4" stroke-linecap="round" />
  <path d="M19 26 C 13 24, 11 19, 13 16 C 17 16, 19 21, 19 26 Z" fill="#4C8B3E" stroke="#1E3E1A" stroke-width="1.4" stroke-linejoin="round" />
  <path d="M19 23 C 25 21, 27 16, 25 13 C 21 13, 19 18, 19 23 Z" fill="#7FA668" stroke="#1E3E1A" stroke-width="1.4" stroke-linejoin="round" />
  <path d="M35 29 L 35 21" stroke="#684813" stroke-width="1.5" stroke-linecap="round" />
  <path d="M35 21 L 41 23.5 L 35 26 Z" fill="#E2735D" />
</svg>`;
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
        if (!requestToastEl) {
            alert(message);
            return;
        }
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
                updateBadge();
            } else {
                console.error("API error:", res.message);
            }
        } catch (err) {
            console.error("Failed to load requests:", err);
        }
    }

    function updateBadge() {
        const badge = document.getElementById('requestsCountBadge');
        if (badge) {
            badge.innerText = requestsList.length;
            if (requestsList.length > 0) {
                badge.classList.remove('d-none');
            } else {
                badge.classList.add('d-none');
            }
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
            if (currentFilter !== 'all') {
                if (currentFilter === 'rejected') {
                    if (req.status !== 'rejected' && req.status !== 'declined') return false;
                } else if (req.status !== currentFilter) {
                    return false;
                }
            }
            // Search filter
            if (searchQuery.trim() !== '') {
                const q = searchQuery.toLowerCase();
                const matchLand = (req.land_title || '').toLowerCase().includes(q);
                const matchOwner = (req.landowner || '').toLowerCase().includes(q);
                const matchPlot = (req.plot_num || '').toLowerCase().includes(q);
                const matchPurpose = (req.purpose || '').toLowerCase().includes(q);
                const matchAddress = (req.land_address || '').toLowerCase().includes(q);
                return matchLand || matchOwner || matchPlot || matchPurpose || matchAddress;
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
                        <span class="fw-semibold">No applications found</span>
                        <p class="small text-muted mb-0 mt-1">There are no plot requests matching the current filter or search criteria.</p>
                    </div>
                `;
            } else {
                requestsTableBody.innerHTML = filtered.map(req => {
                    const time = timeAgo(req.requested_at);
                    const iconHtml = getLandIconSvg();

                    let statusBadge = '';
                    if (req.status === 'approved') {
                        statusBadge = `<span class="badge request-badge-approved rounded-pill px-2.5 py-1.5 fw-semibold d-inline-flex align-items-center gap-1"><i class="bi bi-check-circle-fill"></i> Approved</span>`;
                    } else if (req.status === 'pending') {
                        statusBadge = `<span class="badge request-badge-pending rounded-pill px-2.5 py-1.5 fw-semibold d-inline-flex align-items-center gap-1"><i class="bi bi-clock-history"></i> Pending</span>`;
                    } else if (req.status === 'offer_sent') {
                        const monthlyRate = req.offered_rental_rate ? Math.round(parseFloat(req.offered_rental_rate) / 12) : 9167;
                        const rateStr = `₱${monthlyRate.toLocaleString()}/mo`;
                        statusBadge = `<span class="badge rounded-pill px-2.5 py-1.5 fw-semibold d-inline-flex align-items-center gap-1" style="background:#FEF3C7;color:#92400E;border:1px solid #FCD34D;" title="Offer: ${rateStr}"><i class="bi bi-tag-fill text-warning"></i> Offer (${rateStr})</span>`;
                    } else if (req.status === 'counter_offered') {
                        const counterMonthly = req.counter_rental_rate ? Math.round(parseFloat(req.counter_rental_rate) / 12) : 0;
                        const counterRateStr = counterMonthly ? `₱${counterMonthly.toLocaleString()}/mo` : '₱.../mo';
                        statusBadge = `<span class="badge rounded-pill px-2.5 py-1.5 fw-semibold d-inline-flex align-items-center gap-1" style="background:#E0F2FE;color:#0369A1;border:1px solid #BAE6FD;" title="Counter: ${counterRateStr}"><i class="bi bi-arrow-left-right text-primary"></i> Counter: ${counterRateStr}</span>`;
                    } else if (req.status === 'declined') {
                        statusBadge = `<span class="badge rounded-pill px-2.5 py-1.5 fw-semibold d-inline-flex align-items-center gap-1 bg-danger-subtle text-danger border border-danger-subtle"><i class="bi bi-x-circle-fill"></i> Declined</span>`;
                    } else {
                        statusBadge = `<span class="badge request-badge-rejected rounded-pill px-2.5 py-1.5 fw-semibold d-inline-flex align-items-center gap-1" title="Feedback: ${escapeHtml(req.notes || '')}"><i class="bi bi-x-circle-fill"></i> Rejected</span>`;
                    }

                    // Desktop action buttons
                    let actionsHtml = `
                        <button onclick="openDetailsModal(${req.id})" class="request-action-btn request-btn-view flex-shrink-0" title="View application details">
                            <i class="bi bi-eye"></i>
                        </button>
                    `;

                    if (req.status === 'offer_sent') {
                        actionsHtml += `
                            <button onclick="decideRentalOffer(${req.id}, 'approved')" class="btn btn-sm btn-success rounded-pill px-2.5 py-1 text-xs fw-semibold text-white d-inline-flex align-items-center gap-1 shadow-xs flex-shrink-0" title="Approve & Lease Plot">
                                <i class="bi bi-check-circle-fill"></i> Approve
                            </button>
                            <button onclick="openCounterOfferPrompt(${req.id})" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-1 text-xs fw-semibold flex-shrink-0" title="Propose Counter-Offer">
                                <i class="bi bi-arrow-left-right"></i> Counter
                            </button>
                            <button onclick="decideRentalOffer(${req.id}, 'declined')" class="request-action-btn request-btn-reject flex-shrink-0" title="Decline Offer">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        `;
                    } else if (req.status === 'counter_offered') {
                        actionsHtml += `
                            <span class="text-[11px] text-muted font-medium text-nowrap d-inline-flex align-items-center gap-1 flex-shrink-0" title="Awaiting landowner response">
                                <i class="bi bi-hourglass-split text-primary"></i> Awaiting Owner
                            </span>
                        `;
                    }

                    return `
                        <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom request-table-row">
                            <!-- Land & Owner -->
                            <div style="width: 23%; min-width: 170px;" class="d-flex align-items-center gap-2.5 min-w-0 pe-2">
                                <div class="flex-shrink-0" style="width: 40px; height: 40px;">
                                    ${iconHtml}
                                </div>
                                <div class="min-w-0">
                                    <div class="fw-bold text-dark text-truncate">${req.land_title}</div>
                                    <div class="text-muted small text-truncate" style="font-size: 11px;">
                                        ${req.landowner ? `Managed by ${req.landowner}` : (req.land_address || 'Community Land')}
                                    </div>
                                </div>
                            </div>

                            <!-- Plot & Details -->
                            <div style="width: 23%; min-width: 170px;" class="pe-2 min-w-0">
                                <div class="d-flex align-items-center gap-1.5 min-w-0">
                                    <span class="badge bg-light text-secondary border rounded-pill px-2 py-0.5 flex-shrink-0" style="font-size: 10px;">
                                        <i class="bi bi-grid-3x3 text-success me-1"></i>${req.plot_num}
                                    </span>
                                    <span class="text-muted text-truncate" style="font-size: 11px;">• ${time}</span>
                                </div>
                                <div class="text-muted small text-truncate mt-0.5" style="font-size: 11px;" title="${escapeHtml(req.land_address || req.land_title)}">
                                    <i class="bi bi-geo-alt text-success me-1 flex-shrink-0"></i>${escapeHtml(req.land_address || req.land_title)}
                                </div>
                            </div>

                            <!-- Purpose / Term -->
                            <div style="width: 17%; min-width: 130px;" class="pe-2 min-w-0">
                                <span class="badge bg-light text-dark border rounded-pill px-2 py-1 mb-1 font-monospace" style="font-size: 10px;">
                                    ${req.duration}
                                </span>
                                <div class="text-muted text-truncate" style="font-size: 11px; max-width: 160px;" title="${escapeHtml(req.purpose)}">
                                    ${escapeHtml(req.purpose)}
                                </div>
                            </div>

                            <!-- Status -->
                            <div style="width: 13%; min-width: 100px;" class="pe-2">
                                ${statusBadge}
                            </div>

                            <!-- Actions -->
                            <div style="width: 24%; min-width: 215px;" class="text-end d-flex align-items-center justify-content-end gap-1 flex-shrink-0 flex-nowrap pe-1">
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
                        <span class="fw-semibold">No applications found</span>
                        <p class="small text-muted mb-0 mt-1">There are no plot requests matching the current filter or search criteria.</p>
                    </div>
                `;
            } else {
                requestsMobileCards.innerHTML = filtered.map(req => {
                    const time = timeAgo(req.requested_at);
                    const iconHtml = getLandIconSvg();

                    let statusBadge = '';
                    if (req.status === 'approved') {
                        statusBadge = `<span class="badge request-badge-approved rounded-pill px-2.5 py-1 text-xs fw-semibold"><i class="bi bi-check-circle-fill me-1"></i>Approved</span>`;
                    } else if (req.status === 'pending') {
                        statusBadge = `<span class="badge request-badge-pending rounded-pill px-2.5 py-1 text-xs fw-semibold"><i class="bi bi-clock-history me-1"></i>Pending</span>`;
                    } else if (req.status === 'offer_sent') {
                        const rateStr = req.offered_rental_rate ? `₱${parseFloat(req.offered_rental_rate).toLocaleString()}/yr` : '₱110,000/yr';
                        statusBadge = `<span class="badge rounded-pill px-2.5 py-1 text-xs fw-semibold" style="background:#FEF3C7;color:#92400E;border:1px solid #FCD34D;" title="Offer ${rateStr} received"><i class="bi bi-tag-fill me-1 text-warning"></i>Offer Received</span>`;
                    } else if (req.status === 'counter_offered') {
                        const counterRateStr = req.counter_rental_rate ? `₱${parseFloat(req.counter_rental_rate).toLocaleString()}/yr` : '₱.../yr';
                        statusBadge = `<span class="badge rounded-pill px-2.5 py-1 text-xs fw-semibold" style="background:#E0F2FE;color:#0369A1;border:1px solid #BAE6FD;"><i class="bi bi-arrow-left-right me-1 text-primary"></i>Counter: ${counterRateStr}</span>`;
                    } else if (req.status === 'declined') {
                        statusBadge = `<span class="badge rounded-pill px-2.5 py-1 text-xs fw-semibold bg-danger-subtle text-danger border border-danger-subtle"><i class="bi bi-x-circle-fill me-1"></i>Declined</span>`;
                    } else {
                        statusBadge = `<span class="badge request-badge-rejected rounded-pill px-2.5 py-1 text-xs fw-semibold"><i class="bi bi-x-circle-fill me-1"></i>Rejected</span>`;
                    }

                    let mobileActions = '';
                    if (req.status === 'offer_sent') {
                        const monthlyRate = req.offered_rental_rate ? Math.round(parseFloat(req.offered_rental_rate) / 12) : 9167;
                        const rateStr = `₱${monthlyRate.toLocaleString()}/mo`;
                        mobileActions = `
                            <div class="d-flex align-items-center gap-1.5 pt-2 border-top w-100 flex-wrap" onclick="event.stopPropagation()">
                                <button onclick="decideRentalOffer(${req.id}, 'approved')" class="btn btn-sm btn-success rounded-pill px-2.5 py-1.5 flex-grow-1 text-white fw-semibold shadow-xs" style="font-size: 0.78rem;">
                                    <i class="bi bi-check-circle-fill me-1"></i> Approve (${rateStr})
                                </button>
                                <button onclick="openCounterOfferPrompt(${req.id})" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-1.5 fw-semibold" style="font-size: 0.78rem;">
                                    <i class="bi bi-arrow-left-right me-1"></i> Counter
                                </button>
                                <button onclick="decideRentalOffer(${req.id}, 'declined')" class="btn btn-sm btn-outline-danger rounded-pill px-2.5 py-1.5 fw-semibold" style="font-size: 0.78rem;">
                                    <i class="bi bi-x-lg me-1"></i> Decline
                                </button>
                            </div>
                        `;
                    } else if (req.status === 'counter_offered') {
                        const counterMonthly = req.counter_rental_rate ? Math.round(parseFloat(req.counter_rental_rate) / 12) : 0;
                        const counterRateStr = counterMonthly ? `₱${counterMonthly.toLocaleString()}/mo` : '';
                        mobileActions = `
                            <div class="pt-2 border-top w-100 text-center" onclick="event.stopPropagation()">
                                <span class="badge bg-light text-primary border rounded-pill px-3 py-1.5 text-xs fw-medium d-inline-flex align-items-center gap-1.5">
                                    <i class="bi bi-hourglass-split text-primary"></i> Counter Sent (${counterRateStr}) • Awaiting Landowner
                                </span>
                            </div>
                        `;
                    }

                    return `
                        <div class="request-mobile-card shadow-xs" onclick="openDetailsModal(${req.id})" role="button" tabindex="0" style="cursor: pointer;">
                            <!-- Card Header: Avatar/Icon, Title, Status -->
                            <div class="d-flex align-items-center justify-content-between mb-2.5">
                                <div class="d-flex align-items-center gap-2 min-w-0">
                                    <div class="flex-shrink-0" style="width: 34px; height: 34px;">
                                        ${iconHtml}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="fw-bold text-dark text-truncate" style="font-size: 0.9rem;">${req.land_title}</div>
                                        <div class="text-muted" style="font-size: 11px;">${time} • ${req.landowner ? `Managed by ${req.landowner}` : 'Land Plot'}</div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-1.5">
                                    ${statusBadge}
                                    <i class="bi bi-chevron-right text-muted opacity-50 ms-1" style="font-size: 0.75rem;"></i>
                                </div>
                            </div>

                            <!-- Land & Plot meta -->
                            <div class="p-2.5 bg-light rounded-3 mb-2.5 border border-light-subtle">
                                <div class="d-flex align-items-center justify-content-between mb-1 min-w-0">
                                    <div class="min-w-0 flex-grow-1 me-2">
                                        <strong class="text-dark small text-truncate d-block" title="${escapeHtml(req.land_address || req.land_title)}">
                                            <i class="bi bi-geo-alt text-success me-1"></i>${escapeHtml(req.land_address || req.land_title)}
                                        </strong>
                                    </div>
                                    <span class="badge bg-white text-success border rounded-pill px-2 py-0.5 flex-shrink-0" style="font-size: 10px;">${req.plot_num}</span>
                                </div>
                                <div class="text-muted small mt-1" style="font-size: 0.78rem;">
                                    <i class="bi bi-calendar3 me-1"></i>Term: <strong class="text-dark">${req.duration}</strong>
                                </div>
                            </div>

                            <!-- Purpose Quote -->
                            <div class="mb-2 text-secondary" style="font-size: 0.82rem; line-height: 1.4;">
                                "${escapeHtml(req.purpose)}"
                            </div>

                            <!-- Footer Actions -->
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
            const filter = this.getAttribute('data-filter');
            // Toggle filter: clicking active filter returns to default ('all')
            if (currentFilter === filter) {
                currentFilter = 'all';
            } else {
                currentFilter = filter;
            }

            // Sync all filter buttons across desktop and mobile
            document.querySelectorAll('.request-filter-btn').forEach(b => {
                if (currentFilter !== 'all' && b.getAttribute('data-filter') === currentFilter) {
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

    // Global filter helper for onclick attributes
    window.filterGardenerRequests = function(statusKey) {
        if (currentFilter === statusKey) {
            currentFilter = 'all';
        } else {
            currentFilter = statusKey;
        }
        document.querySelectorAll('.request-filter-btn').forEach(b => {
            if (currentFilter !== 'all' && b.getAttribute('data-filter') === currentFilter) {
                b.classList.add('active');
                b.classList.remove('text-secondary', 'bg-white');
            } else {
                b.classList.remove('active');
                b.classList.add('text-secondary');
            }
        });
        renderRequests();
    };

    // Search bar event listeners (keeps desktop & mobile in sync)
    if (desktopSearch) {
        desktopSearch.addEventListener('input', function () {
            searchQuery = this.value;
            if (mobileSearch && mobileSearch !== this) mobileSearch.value = this.value;
            renderRequests();
        });
    }

    if (mobileSearch) {
        mobileSearch.addEventListener('input', function () {
            searchQuery = this.value;
            if (desktopSearch && desktopSearch !== this) desktopSearch.value = this.value;
            renderRequests();
        });
    }

    // Details Modal Map Instance
    let _detailsModalMap = null;

    window.openDetailsModal = function (id) {
        const req = requestsList.find(r => r.id === id);
        if (!req) return;

        window._activeGardenerReqId = req.id;

        // Avatar & Header Details (Mirroring Landowner Request Modal)
        const avatarEl = document.getElementById('modal_avatar');
        if (avatarEl) {
            const name = req.landowner || req.land_title || 'LD';
            const parts = name.trim().split(/\s+/);
            avatarEl.innerText = parts.length > 1 ? (parts[0][0] + parts[1][0]).toUpperCase() : name.substring(0, 2).toUpperCase();
        }

        const reqTitleEl = document.getElementById('modal_request_title');
        if (reqTitleEl) reqTitleEl.innerText = 'Request Details';

        const reqDateEl = document.getElementById('modal_request_date');
        if (reqDateEl) reqDateEl.innerText = req.requested_at ? `Submitted ${timeAgo(req.requested_at)}` : '';

        // Land & Landowner Info Card
        const titleEl = document.getElementById('modal_land_title');
        if (titleEl) titleEl.innerText = req.land_title;

        const contactEl = document.getElementById('modal_landowner_contact');
        if (contactEl) {
            const ownerHtml = req.landowner ? `<span><i class="bi bi-person text-success me-1"></i>${escapeHtml(req.landowner)}</span>` : '';
            const addrHtml = req.land_address ? `<span><i class="bi bi-geo-alt text-success me-1"></i>${escapeHtml(req.land_address)}</span>` : '';
            contactEl.innerHTML = [ownerHtml, addrHtml].filter(Boolean).join(' • ');
        }

        // Plot & Duration 2-Column Row
        const plotEl = document.getElementById('modal_plot');
        if (plotEl) {
            plotEl.innerHTML = `<i class="bi bi-geo-alt text-success me-1"></i>${escapeHtml(req.land_title)} <span class="badge bg-white text-secondary border ms-1">${escapeHtml(req.plot_num)}</span>`;
        }

        const durationEl = document.getElementById('modal_duration');
        if (durationEl) durationEl.innerText = req.duration;

        // Purpose
        const purposeEl = document.getElementById('modal_purpose');
        if (purposeEl) purposeEl.innerText = req.purpose || 'No description provided.';

        // Status Badge in Info Card (Mirroring Landowner)
        const statusBadgeEl = document.getElementById('modal_status_badge');
        if (statusBadgeEl) {
            if (req.status === 'approved') {
                statusBadgeEl.innerHTML = `<span class="badge request-badge-approved rounded-pill px-2.5 py-1 text-xs"><i class="bi bi-check-circle-fill me-1"></i>Approved</span>`;
            } else if (req.status === 'pending') {
                statusBadgeEl.innerHTML = `<span class="badge request-badge-pending rounded-pill px-2.5 py-1 text-xs"><i class="bi bi-clock-history me-1"></i>Pending</span>`;
            } else if (req.status === 'offer_sent') {
                const monthlyRate = req.offered_rental_rate ? Math.round(parseFloat(req.offered_rental_rate) / 12) : 9167;
                statusBadgeEl.innerHTML = `<span class="badge rounded-pill px-2.5 py-1 text-xs fw-semibold" style="background:#FEF3C7;color:#92400E;border:1px solid #FCD34D;"><i class="bi bi-tag-fill me-1 text-warning"></i>Offer: ₱${monthlyRate.toLocaleString()}/mo</span>`;
            } else if (req.status === 'counter_offered') {
                const counterRate = req.counter_rental_rate ? Math.round(parseFloat(req.counter_rental_rate) / 12) : 0;
                statusBadgeEl.innerHTML = `<span class="badge rounded-pill px-2.5 py-1 text-xs fw-semibold" style="background:#E0F2FE;color:#0369A1;border:1px solid #BAE6FD;"><i class="bi bi-arrow-left-right me-1 text-primary"></i>Counter: ₱${counterRate.toLocaleString()}/mo</span>`;
            } else if (req.status === 'declined') {
                statusBadgeEl.innerHTML = `<span class="badge rounded-pill px-2.5 py-1 text-xs fw-semibold bg-danger-subtle text-danger border border-danger-subtle"><i class="bi bi-x-circle-fill me-1"></i>Declined</span>`;
            } else {
                statusBadgeEl.innerHTML = `<span class="badge request-badge-rejected rounded-pill px-2.5 py-1 text-xs"><i class="bi bi-x-circle-fill me-1"></i>Rejected</span>`;
            }
        }

        // Rental Offer Section
        const offerSection = document.getElementById('modal_offer_section');
        const counterSection = document.getElementById('modal_counter_offered_section');

        if (offerSection) {
            if (req.status === 'offer_sent') {
                offerSection.classList.remove('d-none');
                const annualRate = req.offered_rental_rate ? parseFloat(req.offered_rental_rate) : 110000;
                const monthlyRate = annualRate / 12;
                const rateEl = document.getElementById('modal_offer_rate');
                const durEl = document.getElementById('modal_offer_duration');
                const annualEl = document.getElementById('modal_offer_annual');
                const termsEl = document.getElementById('modal_offer_terms');
                const timeEl = document.getElementById('modal_offer_time');

                if (rateEl) rateEl.innerText = `₱${monthlyRate.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
                if (durEl) durEl.innerText = `/ Month`;
                if (annualEl) annualEl.innerText = `(₱${annualRate.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}/yr)`;
                if (termsEl) termsEl.innerText = req.terms_conditions || 'Standard community plot lease. Organic vegetables, composting, and water access included.';
                if (timeEl) timeEl.innerText = req.requested_at ? timeAgo(req.requested_at) : '';

                // Setup counter inputs default value (monthly fee)
                const counterInput = document.getElementById('modal_counter_rate_input');
                if (counterInput) counterInput.value = Math.round(monthlyRate);
                const counterForm = document.getElementById('modal_counter_form_container');
                if (counterForm) counterForm.classList.add('d-none');
                updateCounterMonthlyCalc();
            } else {
                offerSection.classList.add('d-none');
            }
        }

        if (counterSection) {
            if (req.status === 'counter_offered') {
                counterSection.classList.remove('d-none');
                const cRate = req.counter_rental_rate ? parseFloat(req.counter_rental_rate) : 0;
                const cRateMonthly = cRate / 12;
                const cRateEl = document.getElementById('modal_counter_rate_display');
                const cMonthlyEl = document.getElementById('modal_counter_monthly_display');
                const cNotesEl = document.getElementById('modal_counter_notes_display');

                if (cRateEl) cRateEl.innerText = `₱${cRateMonthly.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
                if (cMonthlyEl) cMonthlyEl.innerText = `(₱${cRate.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}/yr)`;
                if (cNotesEl) cNotesEl.innerText = req.counter_notes ? `"${escapeHtml(req.counter_notes)}"` : 'No counter notes specified.';
            } else {
                counterSection.classList.add('d-none');
            }
        }

        // Feedback section matching Landowner
        const feedbackSection = document.getElementById('modal_feedback_section');
        const feedbackEl = document.getElementById('modal_feedback');
        if (feedbackSection && feedbackEl) {
            if (req.notes) {
                feedbackSection.classList.remove('d-none');
                feedbackEl.className = (req.status === 'rejected' || req.status === 'declined')
                    ? 'alert alert-danger py-2 px-3 mb-0'
                    : 'alert alert-success py-2 px-3 mb-0';
                feedbackEl.innerHTML = `<strong>${req.status === 'rejected' ? 'Rejection Reason:' : 'Feedback:'}</strong> ${escapeHtml(req.notes)}`;
            } else {
                feedbackSection.classList.add('d-none');
            }
        }

        // Modal Footer Action Buttons (Matching Landowner's modalPendingActions)
        const actionButtonsEl = document.getElementById('modalOfferActionButtons');
        if (actionButtonsEl) {
            if (req.status === 'offer_sent') {
                actionButtonsEl.innerHTML = `
                    <button type="button" class="btn btn-outline-danger rounded-pill px-3.5 py-1.5 fw-semibold small" onclick="decideRentalOffer(${req.id}, 'declined')">
                        <i class="bi bi-x-circle me-1"></i> Decline
                    </button>
                    <button type="button" class="btn btn-outline-primary rounded-pill px-3.5 py-1.5 fw-semibold small" onclick="toggleCounterOfferForm()">
                        <i class="bi bi-arrow-left-right me-1"></i> Counter-Offer
                    </button>
                    <button type="button" class="btn btn-success rounded-pill px-3.5 py-1.5 text-white fw-semibold small shadow-xs" onclick="decideRentalOffer(${req.id}, 'approved')">
                        <i class="bi bi-check-circle-fill me-1"></i> Approve &amp; Lease
                    </button>
                `;
            } else if (req.status === 'counter_offered') {
                actionButtonsEl.innerHTML = `
                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle rounded-pill px-3 py-1.5 small d-inline-flex align-items-center gap-1.5">
                        <i class="bi bi-hourglass-split"></i> Counter-Offer Submitted • Awaiting Landowner Response
                    </span>
                `;
            } else if (req.status === 'approved') {
                actionButtonsEl.innerHTML = `
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1.5 small d-inline-flex align-items-center gap-1.5">
                        <i class="bi bi-check-circle-fill"></i> Lease Agreement Active
                    </span>
                `;
            } else if (req.status === 'declined') {
                actionButtonsEl.innerHTML = `
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1.5 small d-inline-flex align-items-center gap-1.5">
                        <i class="bi bi-x-circle-fill"></i> Offer Declined
                    </span>
                `;
            } else if (req.status === 'rejected') {
                actionButtonsEl.innerHTML = `
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1.5 small d-inline-flex align-items-center gap-1.5">
                        <i class="bi bi-x-circle-fill"></i> Application Rejected
                    </span>
                `;
            } else {
                actionButtonsEl.innerHTML = `
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-3 py-1.5 small d-inline-flex align-items-center gap-1.5">
                        <i class="bi bi-clock-history"></i> Awaiting Landowner Review
                    </span>
                `;
            }
        }

        // Land Location Map
        const mapSection = document.getElementById('modal_map_section');
        const lat = parseFloat(req.latitude);
        const lng = parseFloat(req.longitude);
        const hasCoords = !isNaN(lat) && !isNaN(lng) && (lat !== 0 || lng !== 0);

        if (mapSection) {
            if (hasCoords || req.land_address) {
                mapSection.classList.remove('d-none');
                const addrEl = document.getElementById('modal_land_address');
                if (addrEl) addrEl.innerText = req.land_address || 'Coordinates marked on map';

                const coordsEl = document.getElementById('modal_map_coords');
                if (coordsEl) coordsEl.innerText = hasCoords ? `${lat.toFixed(5)}, ${lng.toFixed(5)}` : '';

                const gmapsLink = document.getElementById('modal_gmaps_link');
                if (gmapsLink) {
                    gmapsLink.href = hasCoords
                        ? `https://www.google.com/maps?q=${lat},${lng}`
                        : `https://www.google.com/maps/search/${encodeURIComponent(req.land_title + ' ' + (req.land_address || ''))}`;
                }

                const mapEl = document.getElementById('modalDetailsLeafletMap');
                if (hasCoords && mapEl) {
                    mapEl.parentElement.classList.remove('d-none');
                    renderDetailsModalMap(lat, lng, req.land_title, req.plot_num);
                } else if (mapEl) {
                    mapEl.parentElement.classList.add('d-none');
                }
            } else {
                mapSection.classList.add('d-none');
            }
        }

        if (viewResponseModalEl) {
            const viewModal = bootstrap.Modal.getInstance(viewResponseModalEl) || new bootstrap.Modal(viewResponseModalEl);
            viewModal.show();
        }
    };

    function renderDetailsModalMap(lat, lng, title, plotNum) {
        const initMap = () => {
            const mapEl = document.getElementById('modalDetailsLeafletMap');
            if (!mapEl || typeof L === 'undefined') return;

            if (_detailsModalMap) {
                _detailsModalMap.remove();
                _detailsModalMap = null;
            }

            _detailsModalMap = L.map(mapEl, {
                center: [lat, lng],
                zoom: 16,
                zoomControl: true,
                scrollWheelZoom: false
            });

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap'
            }).addTo(_detailsModalMap);

            L.circle([lat, lng], {
                color: '#2D5A27',
                fillColor: '#7FA668',
                fillOpacity: 0.2,
                radius: 45
            }).addTo(_detailsModalMap);

            const icon = L.divIcon({
                className: '',
                html: `<div style="background:#2D5A27;width:12px;height:12px;border:3px solid #fff;border-radius:50%;box-shadow:0 2px 6px rgba(0,0,0,0.3);"></div>`,
                iconSize: [12, 12]
            });
            L.marker([lat, lng], { icon: icon })
                .bindPopup(`<strong>${title}</strong><br>${plotNum}`)
                .addTo(_detailsModalMap);

            setTimeout(() => {
                if (_detailsModalMap) {
                    _detailsModalMap.invalidateSize();
                }
            }, 120);
        };

        if (viewResponseModalEl && viewResponseModalEl.classList.contains('show')) {
            setTimeout(initMap, 80);
        } else if (viewResponseModalEl) {
            const onShown = function () {
                viewResponseModalEl.removeEventListener('shown.bs.modal', onShown);
                initMap();
            };
            viewResponseModalEl.addEventListener('shown.bs.modal', onShown);
        }
    }

    if (viewResponseModalEl) {
        viewResponseModalEl.addEventListener('hidden.bs.modal', function () {
            if (_detailsModalMap) {
                _detailsModalMap.remove();
                _detailsModalMap = null;
            }
        });
    }

    // Rental Offer Approval/Decline Decision Handler
    window.decideRentalOffer = async function (id, decision) {
        const req = requestsList.find(r => r.id === id);
        const rateStr = req && req.offered_rental_rate
            ? `₱${parseFloat(req.offered_rental_rate).toLocaleString()}/year`
            : '₱110,000/year';

        const confirmMsg = decision === 'approved'
            ? `Do you accept the rental offer of ${rateStr} for ${req ? req.plot_num : 'this plot'}?\n\nBy approving, the lease becomes active and your plot reservation is officially confirmed!`
            : `Are you sure you want to decline this rental offer?`;

        if (!confirm(confirmMsg)) return;

        try {
            const response = await fetch('../api/requests.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'gardener_decision',
                    id: id,
                    decision: decision
                })
            });
            const res = await response.json();
            if (res.status === 'success') {
                if (viewResponseModalEl) {
                    const modal = bootstrap.Modal.getInstance(viewResponseModalEl);
                    if (modal) modal.hide();
                }
                showToast(res.message || (decision === 'approved' ? 'Rental offer approved! Your lease agreement is now active.' : 'Rental offer declined.'));
                await loadRequests();
            } else {
                showToast("Error: " + res.message, false);
            }
        } catch (err) {
            console.error("Rental decision failed:", err);
            showToast("Failed to submit rental decision. Please check your connection.", false);
        }
    };

    // Counter-Offer Handlers for Gardeners
    window.toggleCounterOfferForm = function (force) {
        const container = document.getElementById('modal_counter_form_container');
        if (!container) return;
        if (typeof force === 'boolean') {
            if (force) container.classList.remove('d-none');
            else container.classList.add('d-none');
        } else {
            container.classList.toggle('d-none');
        }
        if (!container.classList.contains('d-none')) {
            const input = document.getElementById('modal_counter_rate_input');
            if (input) {
                input.focus();
                input.select();
            }
        }
    };

    window.updateCounterMonthlyCalc = function () {
        const input = document.getElementById('modal_counter_rate_input');
        const calcEl = document.getElementById('modal_counter_monthly_calc');
        if (!input || !calcEl) return;
        const val = parseFloat(input.value) || 0;
        const annual = val * 12;
        calcEl.textContent = `/ mo (Annual: ~₱${annual.toLocaleString('en-US', { maximumFractionDigits: 0 })}/yr)`;
    };

    window.openCounterOfferPrompt = function (id) {
        window.openDetailsModal(id);
        setTimeout(() => {
            window.toggleCounterOfferForm(true);
        }, 150);
    };

    window.submitCurrentCounterOffer = async function () {
        const reqId = window._activeGardenerReqId;
        if (!reqId) {
            showToast("No active request selected.", false);
            return;
        }

        const rateInput = document.getElementById('modal_counter_rate_input');
        const notesInput = document.getElementById('modal_counter_notes_input');
        const counterMonthly = parseFloat(rateInput ? rateInput.value : 0);
        const counterNotes = notesInput ? notesInput.value.trim() : '';

        if (!counterMonthly || counterMonthly <= 0) {
            showToast("Please enter a valid monthly counter-offer amount (₱ / month).", false);
            if (rateInput) rateInput.focus();
            return;
        }

        const counterAnnual = Math.round(counterMonthly * 12);
        const confirmMsg = `Submit rental counter-offer of ₱${counterMonthly.toLocaleString('en-US', { minimumFractionDigits: 2 })}/month (~₱${counterAnnual.toLocaleString()}/yr) to the landowner?\n\nThis will send your proposed rate for the landowner to review.`;
        if (!confirm(confirmMsg)) return;

        const btn = document.getElementById('btnSubmitCounterOffer');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Submitting...';
        }

        try {
            const response = await fetch('../api/requests.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'counter_offer',
                    id: reqId,
                    counter_rate: counterAnnual,
                    counter_notes: counterNotes
                })
            });
            const res = await response.json();
            if (res.status === 'success') {
                if (viewResponseModalEl) {
                    const modal = bootstrap.Modal.getInstance(viewResponseModalEl);
                    if (modal) modal.hide();
                }
                showToast(res.message || 'Counter-offer submitted successfully!');
                await loadRequests();
            } else {
                showToast("Error: " + res.message, false);
            }
        } catch (err) {
            console.error("Counter-offer submission failed:", err);
            showToast("Failed to submit counter-offer. Please check your connection.", false);
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-send-fill me-1"></i> Send Counter-Offer';
            }
        }
    };

    window.loadRequests = loadRequests;
    loadRequests();
});
