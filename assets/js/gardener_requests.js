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
            approved: requestsList.filter(r => r.status === 'approved').length,
            rejected: requestsList.filter(r => r.status === 'rejected').length
        };

        const elAll = document.getElementById('filterCountAll');
        const elPending = document.getElementById('filterCountPending');
        const elApproved = document.getElementById('filterCountApproved');
        const elRejected = document.getElementById('filterCountRejected');

        if (elAll) elAll.innerText = counts.all;
        if (elPending) elPending.innerText = counts.pending;
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
                label.innerText = `Showing ${statusKey} applications (${count})`;
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
                    <div class="fw-semibold text-dark mb-1">No ${currentFilter} applications found</div>
                    <p class="text-secondary small mb-3">There are currently no requests with "${currentFilter}" status.</p>
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
            } else {
                statusBadge = `<span class="badge request-badge-rejected rounded-pill px-2.5 py-1 text-xs fw-bold d-inline-flex align-items-center gap-1"><i class="bi bi-x-circle-fill"></i> Rejected</span>`;
            }

            const formattedTime = req.requested_at ? timeAgo(req.requested_at) : '';

            return `
                <div class="gardener-request-card" data-id="${req.id}" data-status="${req.status}">
                    <!-- Left: SVG Land Icon -->
                    <div class="gardener-request-icon-wrap" title="${req.land_title} — ${req.plot_num}">
                        ${landIconSvgHtml}
                    </div>

                    <!-- Center/Right Details -->
                    <div class="gardener-request-info">
                        <!-- Row 1: Title & Plot Badge on Left, Status Badge on Right (Single Line!) -->
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

                        <!-- Row 2: Purpose text (Single Line Truncated) -->
                        <p class="gardener-request-purpose mb-1.5 text-truncate" title="${escapeHtml(req.purpose)}">
                            "${escapeHtml(req.purpose)}"
                        </p>

                        <!-- Row 3: Meta Pills on Left + Action Buttons on Right (Single Line!) -->
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

                            <div class="d-flex align-items-center gap-1 flex-shrink-0">
                                ${(req.latitude && req.longitude) ? `
                                <button type="button" onclick="openMapModal(${req.id})" 
                                        class="btn btn-sm rounded-pill px-2.5 py-1 fw-semibold d-inline-flex align-items-center gap-1" 
                                        style="font-size:0.75rem;background:#E8F3F8;color:#204E6E;border:1.5px solid #C4DCEB;"
                                        title="View on Map">
                                    <i class="bi bi-geo-alt-fill"></i>
                                    <span class="d-none d-sm-inline">Map</span>
                                </button>` : ''}
                                <button type="button" onclick="openDetailsModal(${req.id})" 
                                        class="btn btn-sm btn-drive-secondary rounded-pill px-2.5 py-1 fw-semibold d-inline-flex align-items-center gap-1 shadow-xs" 
                                        style="font-size:0.75rem;"
                                        title="Details">
                                    <i class="bi bi-eye"></i>
                                    <span class="d-none d-sm-inline">Details</span>
                                </button>
                            </div>
                        </div>
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
            feedbackSection.classList.remove('d-none');
            feedbackSection.classList.remove('hidden');
            document.getElementById('modal_feedback').innerText = req.notes;
        } else {
            feedbackSection.classList.add('d-none');
        }

        const viewModal = new bootstrap.Modal(viewResponseModalEl);
        viewModal.show();
    };

    // ─── View on Map Modal ───────────────────────────────────────────────────
    function ensureMapModal() {
        if (document.getElementById('requestMapModal')) return;
        const html = `
        <div class="modal fade" id="requestMapModal" tabindex="-1" aria-hidden="true" style="z-index:10060;">
            <div class="modal-dialog modal-dialog-centered" style="max-width:600px;">
                <div class="modal-content border-0 shadow-lg" style="border-radius:24px;overflow:hidden;">
                    <div class="modal-header border-0 pb-1 px-4 pt-4">
                        <div class="min-w-0">
                            <span class="badge rounded-pill mb-1 fw-bold" style="background:#E8F3F8;color:#204E6E;border:1px solid #C4DCEB;font-size:0.68rem;">LAND LOCATION</span>
                            <h5 class="modal-title fw-bold text-dark mb-0" id="mapModalTitle" style="font-size:1rem;"></h5>
                            <p class="text-secondary mb-0" id="mapModalAddress" style="font-size:0.78rem;"></p>
                        </div>
                        <button type="button" class="btn-close ms-2" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-0">
                        <div id="requestLeafletMap" style="height:340px;width:100%;z-index:1;"></div>
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4 pt-3 justify-content-between">
                        <span class="text-muted" id="mapModalCoords" style="font-size:0.72rem;"></span>
                        <a id="mapModalGmapsLink" href="#" target="_blank" rel="noopener"
                           class="btn btn-sm rounded-pill px-3.5 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5"
                           style="background:#E8F3F8;color:#204E6E;border:1.5px solid #C4DCEB;font-size:0.78rem;">
                            <i class="bi bi-box-arrow-up-right"></i> Open in Google Maps
                        </a>
                    </div>
                </div>
            </div>
        </div>`;
        document.body.insertAdjacentHTML('beforeend', html);
    }

    let _reqLeafletMap = null;
    let _reqLeafletCircle = null;
    let _reqLeafletMarker = null;

    window.openMapModal = function(id) {
        const req = requestsList.find(r => r.id === id);
        if (!req) return;

        ensureMapModal();

        // Populate header
        document.getElementById('mapModalTitle').innerText = req.land_title + ' — ' + req.plot_num;
        document.getElementById('mapModalAddress').innerText = req.land_address || '';
        document.getElementById('mapModalCoords').innerText = req.latitude && req.longitude
            ? `${req.latitude.toFixed(6)}, ${req.longitude.toFixed(6)}`
            : '';
        const gmapLink = document.getElementById('mapModalGmapsLink');
        gmapLink.href = req.latitude && req.longitude
            ? `https://www.google.com/maps?q=${req.latitude},${req.longitude}`
            : `https://www.google.com/maps/search/${encodeURIComponent(req.land_title + ' ' + (req.land_address || ''))}`;

        const mapModal = new bootstrap.Modal(document.getElementById('requestMapModal'));
        mapModal.show();

        // Init Leaflet after modal becomes visible
        document.getElementById('requestMapModal').addEventListener('shown.bs.modal', function handler() {
            this.removeEventListener('shown.bs.modal', handler);
            const lat = req.latitude || 14.5995;
            const lng = req.longitude || 120.9842;
            const mapEl = document.getElementById('requestLeafletMap');

            if (_reqLeafletMap) {
                _reqLeafletMap.remove();
                _reqLeafletMap = null;
            }

            _reqLeafletMap = L.map(mapEl, {
                center: [lat, lng],
                zoom: 16,
                zoomControl: true,
                scrollWheelZoom: false
            });

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap contributors'
            }).addTo(_reqLeafletMap);

            // Soft circle for plot area
            L.circle([lat, lng], {
                color: '#2D5A27',
                fillColor: '#7FA668',
                fillOpacity: 0.18,
                weight: 2,
                radius: 40
            }).addTo(_reqLeafletMap);

            // Custom green marker
            const pinIcon = L.divIcon({
                className: '',
                html: `<div style="width:32px;height:32px;background:#2D5A27;border-radius:50% 50% 50% 0;transform:rotate(-45deg);border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,0.25);"></div>`,
                iconSize: [32, 32],
                iconAnchor: [16, 32]
            });
            L.marker([lat, lng], { icon: pinIcon })
                .bindPopup(`<strong>${req.land_title}</strong><br>${req.plot_num}`)
                .addTo(_reqLeafletMap)
                .openPopup();

            _reqLeafletMap.invalidateSize();
        });
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

    window.loadRequests = loadRequests;

    loadRequests();
});
