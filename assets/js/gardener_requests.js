function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

document.addEventListener("DOMContentLoaded", function() {
    let requestsList = [];
    let currentFilter = 'all';

    // DOM elements
    const requestsTableBody = document.getElementById('requestsTableBody');
    const viewResponseModalEl = document.getElementById('viewResponseModal');

    function getLandIconSvg() {
        return `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="48" height="48" class="gardener-request-icon-img" aria-label="Land Plot">
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
        if (isNaN(d)) return dateStr;
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

    async function loadRequests() {
        if (!requestsTableBody) return;
        try {
            const response = await fetch('../api/requests.php');
            const res = await response.json();
            if (res.status === 'success') {
                requestsList = res.data.requests;
                updateFilterCounters();
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

    function updateFilterCounters() {
        const counts = {
            all: requestsList.length,
            pending: requestsList.filter(r => r.status === 'pending').length,
            offer_sent: requestsList.filter(r => r.status === 'offer_sent').length,
            approved: requestsList.filter(r => r.status === 'approved').length,
            rejected: requestsList.filter(r => r.status === 'rejected' || r.status === 'declined').length
        };

        const elAll = document.getElementById('filterCountAll');
        const elPending = document.getElementById('filterCountPending');
        const elOffer = document.getElementById('filterCountOffer');
        const elApproved = document.getElementById('filterCountApproved');
        const elRejected = document.getElementById('filterCountRejected');

        if (elAll) elAll.innerText = counts.all;
        if (elPending) elPending.innerText = counts.pending;
        if (elOffer) elOffer.innerText = counts.offer_sent;
        if (elApproved) elApproved.innerText = counts.approved;
        if (elRejected) elRejected.innerText = counts.rejected;
    }

    window.filterGardenerRequests = function(statusKey) {
        currentFilter = statusKey;

        // Toggle active chip style
        const chips = document.querySelectorAll('.request-filter-chip');
        chips.forEach(chip => {
            if (chip.getAttribute('data-filter') === statusKey) {
                chip.classList.add('active');
            } else {
                chip.classList.remove('active');
            }
        });

        const label = document.getElementById('requestsCountLabel');
        if (label) {
            if (statusKey === 'all') {
                label.innerText = `Showing all applications (${requestsList.length})`;
            } else {
                const count = requestsList.filter(r => r.status === statusKey).length;
                label.innerText = `Showing ${statusKey.replace('_', ' ')} applications (${count})`;
            }
        }

        renderRequests();
    };

    function renderRequests() {
        if (!requestsTableBody) return;

        if (requestsList.length === 0) {
            requestsTableBody.innerHTML = `
                <div class="text-center py-5 px-3 bg-white rounded-4 border border-light-subtle shadow-xs">
                    <div class="rounded-circle bg-light d-inline-flex p-3 mb-2">
                        <i class="bi bi-send text-secondary" style="font-size: 2rem;"></i>
                    </div>
                    <div class="fw-semibold text-dark mb-1">No Plot Applications Yet</div>
                    <p class="text-secondary small mb-3">Browse available community lands and request your preferred plot.</p>
                    <button type="button" class="btn btn-drive-primary btn-sm rounded-pill px-3.5 py-1.5 fw-semibold" onclick="typeof switchGardenerTab === 'function' ? switchGardenerTab('browse') : (window.location.href='browse.php')">
                        <i class="bi bi-compass me-1"></i> Browse Lands
                    </button>
                </div>
            `;
            return;
        }

        const filteredList = currentFilter === 'all'
            ? requestsList
            : requestsList.filter(r => r.status === currentFilter);

        if (filteredList.length === 0) {
            requestsTableBody.innerHTML = `
                <div class="text-center py-5 px-3 bg-white rounded-4 border border-light-subtle shadow-xs">
                    <i class="bi bi-filter-circle fs-1 text-secondary opacity-50 d-block mb-2"></i>
                    <div class="fw-semibold text-dark mb-1">No ${currentFilter.replace('_', ' ')} applications found</div>
                    <p class="text-secondary small mb-3">There are currently no requests with "${currentFilter.replace('_', ' ')}" status.</p>
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3.5 py-1.5 fw-semibold" onclick="filterGardenerRequests('all')">
                        View All Applications
                    </button>
                </div>
            `;
            return;
        }

        const landIconSvgHtml = getLandIconSvg();

        requestsTableBody.innerHTML = filteredList.map(req => {
            let statusBadge = '';
            if (req.status === 'approved') {
                statusBadge = `<span class="badge request-badge-approved rounded-pill px-2.5 py-1 text-xs fw-bold d-inline-flex align-items-center gap-1"><i class="bi bi-check-circle-fill"></i> Approved</span>`;
            } else if (req.status === 'pending') {
                statusBadge = `<span class="badge request-badge-pending rounded-pill px-2.5 py-1 text-xs fw-bold d-inline-flex align-items-center gap-1"><i class="bi bi-clock-history"></i> Pending</span>`;
            } else if (req.status === 'offer_sent') {
                const rateStr = req.offered_rental_rate ? `₱${parseFloat(req.offered_rental_rate).toLocaleString()}/yr` : '₱110,000/yr';
                statusBadge = `<span class="badge rounded-pill px-2.5 py-1 text-xs fw-bold d-inline-flex align-items-center gap-1" style="background:#FEF3C7;color:#92400E;border:1px solid #FCD34D;" title="Rental Offer: ${rateStr}"><i class="bi bi-tag-fill text-warning"></i> Offer Received</span>`;
            } else if (req.status === 'counter_offered') {
                const counterRateStr = req.counter_rental_rate ? `₱${parseFloat(req.counter_rental_rate).toLocaleString()}/yr` : '₱.../yr';
                statusBadge = `<span class="badge rounded-pill px-2.5 py-1 text-xs fw-bold d-inline-flex align-items-center gap-1" style="background:#E0F2FE;color:#0369A1;border:1px solid #BAE6FD;" title="Counter-Offer: ${counterRateStr}"><i class="bi bi-arrow-left-right text-primary"></i> Counter Sent</span>`;
            } else if (req.status === 'declined') {
                statusBadge = `<span class="badge rounded-pill px-2.5 py-1 text-xs fw-bold bg-danger-subtle text-danger border border-danger-subtle d-inline-flex align-items-center gap-1"><i class="bi bi-x-circle-fill"></i> Declined</span>`;
            } else {
                statusBadge = `<span class="badge request-badge-rejected rounded-pill px-2.5 py-1 text-xs fw-bold d-inline-flex align-items-center gap-1"><i class="bi bi-x-circle-fill"></i> Rejected</span>`;
            }

            const formattedTime = req.requested_at ? timeAgo(req.requested_at) : '';

            // If offer received, show action banner on card
            let offerActionBanner = '';
            if (req.status === 'offer_sent') {
                const rateStr = req.offered_rental_rate ? `₱${parseFloat(req.offered_rental_rate).toLocaleString()}/yr` : '₱110,000/yr';
                offerActionBanner = `
                    <div class="mt-2.5 pt-2 border-top d-flex align-items-center justify-content-between flex-wrap gap-2" onclick="event.stopPropagation()">
                        <div class="small fw-semibold text-dark d-flex align-items-center gap-1">
                            <span class="text-secondary" style="font-size:11px;">Rental Rate:</span>
                            <span class="text-success fw-bold">${rateStr}</span>
                            <span class="text-muted" style="font-size:10px;">(${req.rental_duration || '1 Year'})</span>
                        </div>
                        <div class="d-flex align-items-center gap-1.5 ms-auto">
                            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2.5 py-1 text-xs fw-semibold" onclick="decideRentalOffer(${req.id}, 'declined')">
                                <i class="bi bi-x-circle me-1"></i> Decline
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-1 text-xs fw-semibold" onclick="openCounterOfferPrompt(${req.id})">
                                <i class="bi bi-arrow-left-right me-1"></i> Counter
                            </button>
                            <button type="button" class="btn btn-sm btn-success rounded-pill px-3 py-1 text-xs fw-semibold text-white shadow-xs" onclick="decideRentalOffer(${req.id}, 'approved')">
                                <i class="bi bi-check-circle-fill me-1"></i> Approve
                            </button>
                        </div>
                    </div>
                `;
            } else if (req.status === 'counter_offered') {
                const counterRateStr = req.counter_rental_rate ? `₱${parseFloat(req.counter_rental_rate).toLocaleString()}/yr` : '₱.../yr';
                offerActionBanner = `
                    <div class="mt-2.5 pt-2 border-top d-flex align-items-center justify-content-between flex-wrap gap-2" onclick="event.stopPropagation()">
                        <div class="small fw-semibold text-dark d-flex align-items-center gap-1">
                            <span class="text-secondary" style="font-size:11px;">Your Counter-Offer:</span>
                            <span class="text-primary fw-bold">${counterRateStr}</span>
                        </div>
                        <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1 text-xs fw-medium d-inline-flex align-items-center gap-1">
                            <i class="bi bi-hourglass-split text-primary"></i> Awaiting Owner Decision
                        </span>
                    </div>
                `;
            }

            return `
                <div class="gardener-request-card" data-id="${req.id}" data-status="${req.status}" onclick="openDetailsModal(${req.id})" role="button" tabindex="0">
                    <!-- Left: SVG Land Icon -->
                    <div class="gardener-request-icon-wrap" title="${req.land_title} — ${req.plot_num}">
                        ${landIconSvgHtml}
                    </div>

                    <!-- Center/Right Details -->
                    <div class="gardener-request-info">
                        <!-- Row 1: Title & Plot Badge on Left, Status Badge on Right -->
                        <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                            <div class="d-flex align-items-center gap-1.5 min-w-0">
                                <h3 class="gardener-request-title mb-0 text-truncate">${req.land_title}</h3>
                                <span class="badge rounded-pill px-2 py-0.5 text-xs fw-bold flex-shrink-0" style="background:#EBF3E8;color:#2D5A27;border:1px solid #BDDAB8;font-size:0.7rem;">
                                    ${req.plot_num}
                                </span>
                            </div>
                            <div class="flex-shrink-0">
                                ${statusBadge}
                            </div>
                        </div>

                        <!-- Row 2: Purpose text -->
                        <p class="gardener-request-purpose mb-1.5 text-truncate" title="${escapeHtml(req.purpose)}">
                            "${escapeHtml(req.purpose)}"
                        </p>

                        <!-- Row 3: Meta Pills on Left + Subtle Chevron on Right -->
                        <div class="d-flex align-items-center justify-content-between gap-2">
                            <div class="gardener-request-meta-pills">
                                <span class="badge bg-light text-secondary border rounded-pill px-2 py-0.5 text-xs d-inline-flex align-items-center gap-1">
                                    <i class="bi bi-clock-history text-primary"></i> ${req.duration}
                                </span>
                                ${formattedTime ? `
                                <span class="badge bg-light text-secondary border rounded-pill px-2 py-0.5 text-xs d-inline-flex align-items-center gap-1">
                                    <i class="bi bi-calendar-event text-secondary"></i> ${formattedTime}
                                </span>` : ''}
                                ${req.notes ? `
                                <span class="badge rounded-pill px-2 py-0.5 text-xs d-inline-flex align-items-center gap-1" style="background:#E8F3F8;color:#204E6E;border:1px solid #C4DCEB;">
                                    <i class="bi bi-chat-quote-fill"></i> <span class="d-none d-sm-inline">Landowner</span> Note
                                </span>` : ''}
                            </div>

                            <div class="text-secondary opacity-50 flex-shrink-0" style="font-size:0.75rem;">
                                <i class="bi bi-chevron-right"></i>
                            </div>
                        </div>

                        <!-- Offer Action Banner (if offer_sent) -->
                        ${offerActionBanner}
                    </div>
                </div>
            `;
        }).join('');
    }

    let _detailsModalMap = null;

    window.openDetailsModal = function(id) {
        const req = requestsList.find(r => r.id === id);
        if (!req) return;

        document.getElementById('modal_land_title').innerText = req.land_title + ' — ' + req.plot_num;
        document.getElementById('modal_purpose').innerText = req.purpose;
        document.getElementById('modal_duration').innerText = req.duration;

        let currentGardenerReqId = req.id;
        window._activeGardenerReqId = req.id;

        const statusBadgeEl = document.getElementById('modal_status_badge');
        if (statusBadgeEl) {
            if (req.status === 'approved') {
                statusBadgeEl.innerHTML = `<span class="badge request-badge-approved rounded-pill px-2.5 py-1 text-xs fw-bold"><i class="bi bi-check-circle-fill me-1"></i>Approved</span>`;
            } else if (req.status === 'pending') {
                statusBadgeEl.innerHTML = `<span class="badge request-badge-pending rounded-pill px-2.5 py-1 text-xs fw-bold"><i class="bi bi-clock-history me-1"></i>Pending</span>`;
            } else if (req.status === 'offer_sent') {
                statusBadgeEl.innerHTML = `<span class="badge rounded-pill px-2.5 py-1 text-xs fw-bold" style="background:#FEF3C7;color:#92400E;border:1px solid #FCD34D;"><i class="bi bi-tag-fill me-1 text-warning"></i>Offer Received</span>`;
            } else if (req.status === 'counter_offered') {
                statusBadgeEl.innerHTML = `<span class="badge rounded-pill px-2.5 py-1 text-xs fw-bold" style="background:#E0F2FE;color:#0369A1;border:1px solid #BAE6FD;"><i class="bi bi-arrow-left-right me-1 text-primary"></i>Counter-Offer Sent</span>`;
            } else if (req.status === 'declined') {
                statusBadgeEl.innerHTML = `<span class="badge rounded-pill px-2.5 py-1 text-xs fw-bold bg-danger-subtle text-danger border border-danger-subtle"><i class="bi bi-x-circle-fill me-1"></i>Declined</span>`;
            } else {
                statusBadgeEl.innerHTML = `<span class="badge request-badge-rejected rounded-pill px-2.5 py-1 text-xs fw-bold"><i class="bi bi-x-circle-fill me-1"></i>Rejected</span>`;
            }
        }

        // Rental Offer Section
        const offerSection = document.getElementById('modal_offer_section');
        const counterSection = document.getElementById('modal_counter_offered_section');

        if (offerSection) {
            if (req.status === 'offer_sent') {
                offerSection.classList.remove('d-none');
                const rate = req.offered_rental_rate ? parseFloat(req.offered_rental_rate) : 110000;
                const rateEl = document.getElementById('modal_offer_rate');
                const durEl = document.getElementById('modal_offer_duration');
                const termsEl = document.getElementById('modal_offer_terms');

                if (rateEl) rateEl.innerText = `₱${rate.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
                if (durEl) durEl.innerText = `/ ${req.rental_duration || '1 Year'}`;
                if (termsEl) termsEl.innerText = req.terms_conditions || 'Standard 50 sq. m. community plot lease. Organic vegetables, composting, and water access included.';

                // Setup counter inputs default value
                const counterInput = document.getElementById('modal_counter_rate_input');
                if (counterInput) counterInput.value = rate;
                const counterForm = document.getElementById('modal_counter_form_container');
                if (counterForm) counterForm.classList.add('d-none');
                updateCounterMonthlyCalc();

                const btnApprove = document.getElementById('btnModalApproveOffer');
                if (btnApprove) {
                    btnApprove.onclick = () => window.decideRentalOffer(req.id, 'approved');
                }
                const btnDecline = document.getElementById('btnModalDeclineOffer');
                if (btnDecline) {
                    btnDecline.onclick = () => window.decideRentalOffer(req.id, 'declined');
                }
            } else {
                offerSection.classList.add('d-none');
            }
        }

        if (counterSection) {
            if (req.status === 'counter_offered') {
                counterSection.classList.remove('d-none');
                const cRate = req.counter_rental_rate ? parseFloat(req.counter_rental_rate) : 0;
                const cRateEl = document.getElementById('modal_counter_rate_display');
                const cMonthlyEl = document.getElementById('modal_counter_monthly_display');
                const cNotesEl = document.getElementById('modal_counter_notes_display');

                if (cRateEl) cRateEl.innerText = `₱${cRate.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
                if (cMonthlyEl) cMonthlyEl.innerText = `(₱${(cRate / 12).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}/mo)`;
                if (cNotesEl) cNotesEl.innerText = req.counter_notes ? `"${escapeHtml(req.counter_notes)}"` : 'No counter notes specified.';
            } else {
                counterSection.classList.add('d-none');
            }
        }

        const feedbackSection = document.getElementById('modal_feedback_section');
        if (req.notes) {
            feedbackSection.classList.remove('d-none');
            feedbackSection.classList.remove('hidden');
            document.getElementById('modal_feedback').innerText = req.notes;
        } else {
            feedbackSection.classList.add('d-none');
        }

        // ─── Land Location Map inside Application Details ─────────────────────
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

        const viewModal = new bootstrap.Modal(viewResponseModalEl);
        viewModal.show();
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
                fillOpacity: 0.18,
                weight: 2,
                radius: 40
            }).addTo(_detailsModalMap);

            const pinIcon = L.divIcon({
                className: '',
                html: `<div style="width:28px;height:28px;background:#2D5A27;border-radius:50% 50% 50% 0;transform:rotate(-45deg);border:2.5px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,0.25);"></div>`,
                iconSize: [28, 28],
                iconAnchor: [14, 28]
            });

            L.marker([lat, lng], { icon: pinIcon })
                .bindPopup(`<strong>${title}</strong><br>${plotNum}`)
                .addTo(_detailsModalMap)
                .openPopup();

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

    // Keep openMapModal as an alias so any external calls continue working seamlessly
    window.openMapModal = function(id) {
        window.openDetailsModal(id);
    };

    // ─── Horizontal Scroll Drag & Wheel Support for Filter Chips ───────────
    const filterChipsEl = document.getElementById('requestsFilterChips');
    if (filterChipsEl) {
        filterChipsEl.addEventListener('wheel', (e) => {
            if (e.deltaY !== 0) {
                e.preventDefault();
                filterChipsEl.scrollLeft += e.deltaY;
            }
        }, { passive: false });

        let isDown = false;
        let startX = 0;
        let scrollLeft = 0;
        let hasMoved = false;

        filterChipsEl.addEventListener('mousedown', (e) => {
            isDown = true;
            hasMoved = false;
            startX = e.pageX - filterChipsEl.offsetLeft;
            scrollLeft = filterChipsEl.scrollLeft;
        });

        window.addEventListener('mouseup', () => {
            if (isDown) {
                isDown = false;
                if (filterChipsEl) filterChipsEl.style.cursor = 'grab';
            }
        });

        filterChipsEl.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            const x = e.pageX - filterChipsEl.offsetLeft;
            const walk = (x - startX) * 1.5;
            if (Math.abs(walk) > 4) {
                hasMoved = true;
                filterChipsEl.style.cursor = 'grabbing';
            }
            filterChipsEl.scrollLeft = scrollLeft - walk;
        });

        // Prevent click firing if the user was dragging
        filterChipsEl.addEventListener('click', (e) => {
            if (hasMoved) {
                e.preventDefault();
                e.stopPropagation();
            }
        }, true);
    }

    // ─── Rental Offer Approval/Decline Decision Handler ───────────────────
    window.decideRentalOffer = async function(id, decision) {
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
                // Hide modal if open
                const modal = bootstrap.Modal.getInstance(viewResponseModalEl);
                if (modal) modal.hide();

                alert(res.message || (decision === 'approved' ? 'Rental offer approved! Your lease agreement is now active.' : 'Rental offer declined.'));
                await loadRequests();
            } else {
                alert("Error: " + res.message);
            }
        } catch (err) {
            console.error("Rental decision failed:", err);
            alert("Failed to submit rental decision. Please check your connection.");
        }
    };

    // ─── Counter-Offer Handlers for Gardeners ─────────────────────────────
    window.toggleCounterOfferForm = function(force) {
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

    window.updateCounterMonthlyCalc = function() {
        const input = document.getElementById('modal_counter_rate_input');
        const calcEl = document.getElementById('modal_counter_monthly_calc');
        if (!input || !calcEl) return;
        const val = parseFloat(input.value) || 0;
        const monthly = val > 0 ? (val / 12) : 0;
        calcEl.textContent = `~₱${monthly.toLocaleString('en-US', {maximumFractionDigits: 0})}/mo`;
    };

    window.openCounterOfferPrompt = function(id) {
        window.openDetailsModal(id);
        setTimeout(() => {
            window.toggleCounterOfferForm(true);
        }, 150);
    };

    window.submitCurrentCounterOffer = async function() {
        const reqId = window._activeGardenerReqId;
        if (!reqId) {
            alert("No active request selected.");
            return;
        }

        const rateInput = document.getElementById('modal_counter_rate_input');
        const notesInput = document.getElementById('modal_counter_notes_input');
        const counterRate = parseFloat(rateInput ? rateInput.value : 0);
        const counterNotes = notesInput ? notesInput.value.trim() : '';

        if (!counterRate || counterRate <= 0) {
            alert("Please enter a valid positive counter-offer amount (₱ / year).");
            if (rateInput) rateInput.focus();
            return;
        }

        const confirmMsg = `Submit rental counter-offer of ₱${counterRate.toLocaleString('en-US', {minimumFractionDigits: 2})}/year to the landowner?\n\nThis will send your proposed rate for the landowner to review and accept or adjust.`;
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
                    counter_rate: counterRate,
                    counter_notes: counterNotes
                })
            });
            const res = await response.json();
            if (res.status === 'success') {
                const modal = bootstrap.Modal.getInstance(viewResponseModalEl);
                if (modal) modal.hide();
                alert(res.message || 'Counter-offer submitted successfully!');
                await loadRequests();
            } else {
                alert("Error: " + res.message);
            }
        } catch (err) {
            console.error("Counter-offer submission failed:", err);
            alert("Failed to submit counter-offer. Please check your connection.");
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

