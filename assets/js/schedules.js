document.addEventListener("DOMContentLoaded", function () {

    // ─── Mock Data ────────────────────────────────────────────────────────────
    // ─── Mock Data (Fallback) ──────────────────────────────────────────────────
    const MOCK_SCHEDULES = [
        {
            id: 1,
            title: "Morning Irrigation Round",
            task_type: "watering",
            status: "in_progress",
            land_title: "Plot A – North Rooftop",
            gardener: "Maria Santos",
            start_time: (() => { const d = new Date(); d.setHours(6, 0, 0); return d.toISOString(); })(),
            end_time:   (() => { const d = new Date(); d.setHours(7, 30, 0); return d.toISOString(); })(),
            description: "Water all raised beds in sections B-1 and B-2. Check drip-line pressure valves before starting.",
            xp: 120,
            difficulty: "easy"
        },
        {
            id: 2,
            title: "Seedling Transplant – Cherry Tomatoes",
            task_type: "planting",
            status: "pending",
            land_title: "Plot B – Greenhouse Bay",
            gardener: "Juan dela Cruz",
            start_time: (() => { const d = new Date(); d.setDate(d.getDate() + 1); d.setHours(8, 0, 0); return d.toISOString(); })(),
            end_time:   (() => { const d = new Date(); d.setDate(d.getDate() + 1); d.setHours(10, 30, 0); return d.toISOString(); })(),
            description: "Move cherry tomato seedlings from nursery trays into prepared beds. Spacing: 40 cm apart.",
            xp: 250,
            difficulty: "medium"
        },
        {
            id: 3,
            title: "Weed Clearing – East Fence Line",
            task_type: "weeding",
            status: "pending",
            land_title: "Plot C – East Strip",
            gardener: null,
            start_time: (() => { const d = new Date(); d.setDate(d.getDate() + 2); d.setHours(7, 0, 0); return d.toISOString(); })(),
            end_time:   (() => { const d = new Date(); d.setDate(d.getDate() + 2); d.setHours(9, 0, 0); return d.toISOString(); })(),
            description: "Remove aggressive weed growth along the eastern boundary. Bag all cuttings and transport to compost.",
            xp: 180,
            difficulty: "medium"
        },
        {
            id: 4,
            title: "Corn Harvest – Rows 5–12",
            task_type: "harvesting",
            status: "pending",
            land_title: "Plot D – South Cornfield",
            gardener: "Ana Reyes",
            start_time: (() => { const d = new Date(); d.setDate(d.getDate() + 3); d.setHours(5, 30, 0); return d.toISOString(); })(),
            end_time:   (() => { const d = new Date(); d.setDate(d.getDate() + 3); d.setHours(9, 0, 0); return d.toISOString(); })(),
            description: "Harvest mature corn ears from rows 5 through 12. Sort by size and transport to market storage.",
            xp: 400,
            difficulty: "hard"
        },
        {
            id: 5,
            title: "Woodchip & Straw Mulching",
            task_type: "mulching",
            status: "pending",
            land_title: "Plot A – North Rooftop",
            gardener: "Maria Santos",
            start_time: (() => { const d = new Date(); d.setDate(d.getDate() + 1); d.setHours(9, 0, 0); return d.toISOString(); })(),
            end_time:   (() => { const d = new Date(); d.setDate(d.getDate() + 1); d.setHours(11, 0, 0); return d.toISOString(); })(),
            description: "Spread a 3-inch protective mulch layer around tomato and pepper bases to regulate soil moisture.",
            xp: 150,
            difficulty: "easy"
        },
        {
            id: 6,
            title: "Fruit Tree & Shrub Pruning",
            task_type: "pruning",
            status: "pending",
            land_title: "Plot E – Orchard Row",
            gardener: null,
            start_time: (() => { const d = new Date(); d.setDate(d.getDate() + 5); d.setHours(7, 0, 0); return d.toISOString(); })(),
            end_time:   (() => { const d = new Date(); d.setDate(d.getDate() + 5); d.setHours(10, 0, 0); return d.toISOString(); })(),
            description: "Shape and prune young fruit trees. Remove crossing branches and seal cuts with pruning paste.",
            xp: 220,
            difficulty: "hard"
        },
        {
            id: 7,
            title: "Climbing Vine & Trellis Setup",
            task_type: "trellising",
            status: "pending",
            land_title: "Plot B – Greenhouse Bay",
            gardener: "Carlos Mendoza",
            start_time: (() => { const d = new Date(); d.setDate(d.getDate() + 2); d.setHours(8, 0, 0); return d.toISOString(); })(),
            end_time:   (() => { const d = new Date(); d.setDate(d.getDate() + 2); d.setHours(10, 30, 0); return d.toISOString(); })(),
            description: "Install wooden lattice trellises for climbing pole bean rows. Secure main vines with garden ties.",
            xp: 210,
            difficulty: "medium"
        },
        {
            id: 8,
            title: "Organic Compost Application",
            task_type: "fertilizing",
            status: "completed",
            land_title: "Plot C – East Strip",
            gardener: "Juan dela Cruz",
            start_time: (() => { const d = new Date(); d.setDate(d.getDate() - 1); d.setHours(9, 0, 0); return d.toISOString(); })(),
            end_time:   (() => { const d = new Date(); d.setDate(d.getDate() - 1); d.setHours(11, 0, 0); return d.toISOString(); })(),
            description: "Apply compost mix to raised beds in Plot C. Ratio: 2 kg per sq. meter. Work into topsoil.",
            xp: 300,
            difficulty: "medium"
        },
        {
            id: 9,
            title: "Leaf Inspection & Pest Scouting",
            task_type: "pests",
            status: "pending",
            land_title: "Plot D – South Cornfield",
            gardener: "Ana Reyes",
            start_time: (() => { const d = new Date(); d.setDate(d.getDate() + 3); d.setHours(8, 0, 0); return d.toISOString(); })(),
            end_time:   (() => { const d = new Date(); d.setDate(d.getDate() + 3); d.setHours(9, 30, 0); return d.toISOString(); })(),
            description: "Inspect undersides of squash and bean foliage with magnifying glass for aphids or mites. Release ladybugs.",
            xp: 140,
            difficulty: "easy"
        },
        {
            id: 10,
            title: "Tool Sanitization & Maintenance",
            task_type: "cleaning",
            status: "completed",
            land_title: "Plot E – Tool Shed Bay",
            gardener: "Carlos Mendoza",
            start_time: (() => { const d = new Date(); d.setDate(d.getDate() - 2); d.setHours(14, 0, 0); return d.toISOString(); })(),
            end_time:   (() => { const d = new Date(); d.setDate(d.getDate() - 2); d.setHours(15, 30, 0); return d.toISOString(); })(),
            description: "Clean, disinfect, and sharpen trowels, hoes, and secateurs. Apply protective oil coat to prevent rust.",
            xp: 100,
            difficulty: "easy"
        }
    ];

    // ─── 10 Task Types Config (Using tasksicons.png - Flat Solid Colors) ─────
    const TYPE_CONFIG = {
        watering: {
            label:        "Watering",
            icon:         "watering.png",
            color:        "#5B8FB9",
            badgeBg:      "#E8F1F5",
            badgeColor:   "#2E5266",
            accentBg:     "#F4F8FA",
            accentBorder: "#C8DBE5",
        },
        planting: {
            label:        "Planting",
            icon:         "planting.png",
            color:        "#7FA668",
            badgeBg:      "#E5EFE0",
            badgeColor:   "#385723",
            accentBg:     "#F3F8F0",
            accentBorder: "#C5DCBD",
        },
        weeding: {
            label:        "Weeding",
            icon:         "weeding.png",
            color:        "#DDA15E",
            badgeBg:      "#FDF4E7",
            badgeColor:   "#7A4C15",
            accentBg:     "#FCF8F2",
            accentBorder: "#F3DCBA",
        },
        pruning: {
            label:        "Pruning",
            icon:         "pruning.png",
            color:        "#C86D51",
            badgeBg:      "#F9ECE8",
            badgeColor:   "#7D2F1B",
            accentBg:     "#FCF5F3",
            accentBorder: "#ECC9BF",
        },
        mulching: {
            label:        "Mulching",
            icon:         "mulching.png",
            color:        "#8B5E3C",
            badgeBg:      "#F5ECE5",
            badgeColor:   "#54321A",
            accentBg:     "#FAF5F1",
            accentBorder: "#DBC6B8",
        },
        fertilizing: {
            label:        "Fertilizing",
            icon:         "fertilizing.png",
            color:        "#9A7AA0",
            badgeBg:      "#F4EDF6",
            badgeColor:   "#5B3663",
            accentBg:     "#FAF7FB",
            accentBorder: "#DAC9DD",
        },
        trellising: {
            label:        "Trellising",
            icon:         "trellising.png",
            color:        "#606C38",
            badgeBg:      "#EEF0E5",
            badgeColor:   "#313916",
            accentBg:     "#F7F8F3",
            accentBorder: "#CBD2B6",
        },
        harvesting: {
            label:        "Harvesting",
            icon:         "harvesting.png",
            color:        "#D97443",
            badgeBg:      "#FCEEE7",
            badgeColor:   "#7D320D",
            accentBg:     "#FCF7F3",
            accentBorder: "#F3CCB9",
        },
        pests: {
            label:        "Checking for Pests",
            icon:         "pests.png",
            color:        "#778A35",
            badgeBg:      "#F1F4E6",
            badgeColor:   "#3C4814",
            accentBg:     "#F9FAF3",
            accentBorder: "#D3DDB6",
        },
        cleaning: {
            label:        "Cleaning Tools",
            icon:         "cleaning.png",
            color:        "#52796F",
            badgeBg:      "#E8EFEF",
            badgeColor:   "#223C35",
            accentBg:     "#F4F7F7",
            accentBorder: "#C3D6D2",
        },
    };

    // Aliases
    TYPE_CONFIG.checking_pests = TYPE_CONFIG.pests;
    TYPE_CONFIG.pest_control   = TYPE_CONFIG.pests;
    TYPE_CONFIG.cleaning_tools = TYPE_CONFIG.cleaning;
    TYPE_CONFIG.other          = TYPE_CONFIG.cleaning;

    // Returns the authentic image icon markup extracted from tasksicons.png
    function taskIcon(typeKey) {
        const config = TYPE_CONFIG[typeKey] || TYPE_CONFIG.other || TYPE_CONFIG.watering;
        const iconFile = config.icon || 'watering.png';
        return `<img src="${BASE_PATH}assets/images/tasks/${iconFile}" alt="${config.label}" class="task-icon-img" width="40" height="40" />`;
    }

    // ─── Status Config ────────────────────────────────────────────────────────
    const STATUS_CONFIG = {
        pending:     { label: "Pending",     bg: "#fef9c3", color: "#713f12", dot: "#eab308", icon: "clock" },
        in_progress: { label: "In Progress", bg: "#dbeafe", color: "#1e3a5f", dot: "#3b82f6", icon: "play-circle" },
        completed:   { label: "Completed",   bg: "#dcfce7", color: "#14532d", dot: "#22c55e", icon: "check-circle-2" },
        overdue:     { label: "Overdue",     bg: "#fee2e2", color: "#7f1d1d", dot: "#ef4444", icon: "alert-circle" },
    };

    // ─── Difficulty Config ────────────────────────────────────────────────────
    const DIFFICULTY_CONFIG = {
        easy:   { label: "Easy",   stars: 1, color: "#22c55e" },
        medium: { label: "Medium", stars: 2, color: "#f59e0b" },
        hard:   { label: "Hard",   stars: 3, color: "#ef4444" },
    };

    const BASE_PATH = '../';
    let schedulesList = [];

    // DOM Elements
    const schedulesContainer = document.getElementById('schedulesContainer');
    const addTaskModalEl     = document.getElementById('addTaskModal');
    const addTaskForm        = document.getElementById('addTaskForm');

    // ─── Helpers ──────────────────────────────────────────────────────────────

    function formatDate(str) {
        const d = new Date(str);
        if (isNaN(d)) return str;
        const now = new Date();
        const isToday = d.toDateString() === now.toDateString();
        const isTomorrow = d.toDateString() === new Date(now.getTime() + 86400000).toDateString();
        const dayLabel = isToday ? "Today" : isTomorrow ? "Tomorrow" : d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        const time = d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
        return `${dayLabel} · ${time}`;
    }

    function formatEndTime(str) {
        const d = new Date(str);
        if (isNaN(d)) return str;
        return d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
    }

    function lucideIcon(name, size = 18) {
        return `<i data-lucide="${name}" style="width:${size}px;height:${size}px;stroke-width:2;"></i>`;
    }

    function difficultyStars(diff) {
        const cfg = DIFFICULTY_CONFIG[diff] || DIFFICULTY_CONFIG.easy;
        const stars = Array.from({ length: 3 }, (_, i) =>
            `<svg width="12" height="12" viewBox="0 0 24 24" fill="${i < cfg.stars ? cfg.color : '#d1d5db'}" xmlns="http://www.w3.org/2000/svg">
                <polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26"/>
            </svg>`
        ).join('');
        return `<span class="task-difficulty-stars" title="${cfg.label}">${stars}</span>`;
    }

    // ─── Cats & Soup Heart Meter ──────────────────────────────────────────
    function catsAndSoupHearts(filled = 1, total = 8) {
        let hearts = '';
        for (let i = 0; i < total; i++) {
            if (i < filled) {
                hearts += `<span style="font-size:11px;line-height:1;" title="Heart Affinity">❤️</span>`;
            } else {
                hearts += `<span style="font-size:11px;line-height:1;opacity:0.4;filter:grayscale(1);">🤍</span>`;
            }
        }
        return `<span class="cats-heart-meter d-inline-flex align-items-center gap-0.5 ms-2">${hearts}</span>`;
    }

    // ─── Card Renderer ────────────────────────────────────────────────────────

    function renderCard(sched) {
        const type   = TYPE_CONFIG[sched.task_type] || TYPE_CONFIG.watering;
        const xp     = sched.xp || 100;
        const isCompleted = sched.status === 'completed';

        // Custom inline icon
        const iconContent = taskIcon(sched.task_type);

        // Status pill - ONLY show if completed
        const statusPill = isCompleted
            ? `<span class="task-status-pill task-status-pill--completed">
                    ${lucideIcon('check-circle-2', 12)}
                    Completed
               </span>`
            : '';

        // XP badge
        const xpBadge = `
            <span class="task-xp-badge">
                ${lucideIcon('zap', 12)}
                +${xp} XP
            </span>`;

        // Gardener chip
        const gardenerChip = sched.gardener
            ? `<span class="task-meta-chip">
                    ${lucideIcon('user-round', 13)}
                    ${sched.gardener}
               </span>`
            : `<span class="task-meta-chip task-meta-chip--empty">
                    ${lucideIcon('user-round-x', 13)}
                    Unassigned
               </span>`;

        // Location chip
        const locationChip = `
            <span class="task-meta-chip">
                ${lucideIcon('map-pin', 13)}
                ${sched.land_title}
            </span>`;

        // Time chip
        const timeChip = `
            <span class="task-meta-chip">
                ${lucideIcon('clock-3', 13)}
                ${formatDate(sched.start_time)} – ${formatEndTime(sched.end_time)}
            </span>`;

        return `
        <div class="task-quest-card ${isCompleted ? 'task-quest-card--done' : ''}"
             style="--card-color:${type.color};--card-accent-bg:${type.accentBg};--card-accent-border:${type.accentBorder};">

            <!-- Card Header Band (Cats & Soup Aesthetic) -->
            <div class="task-card-header-band">
                <!-- Squircle Avatar Container -->
                <div class="task-icon-bubble" title="${type.label}">
                    ${iconContent}
                </div>

                <!-- Title block -->
                <div class="task-card-title-block">
                    <span class="task-type-label">${type.label}</span>
                    <h3 class="task-card-title">${sched.title}</h3>
                </div>

                <!-- Badges neatly aligned on the top right -->
                <div class="task-card-badges">
                    ${xpBadge}
                    ${statusPill}
                </div>
            </div>

            <!-- Perforated Dashed Divider -->
            <div class="task-card-divider"></div>

            <!-- Description -->
            <p class="task-card-desc">${sched.description}</p>

            <!-- Meta chips -->
            <div class="task-meta-chips">
                ${timeChip}
                ${locationChip}
                ${gardenerChip}
            </div>

            <!-- Footer (Clean meta hint + action button) -->
            <div class="task-card-footer">
                <div class="task-card-footer-info">
                    ${lucideIcon('clock-3', 13)}
                    <span>${formatDate(sched.start_time)}</span>
                </div>
                ${isCompleted
                    ? `<button type="button" class="task-action-btn task-action-btn--view" onclick="openViewQuestModal(${sched.id})">
                           ${lucideIcon('eye', 14)} View Quest
                       </button>`
                    : `<button type="button" class="task-action-btn task-action-btn--complete" onclick="openCompleteQuestModal(${sched.id})">
                           ${lucideIcon(isLandownerUser() ? 'shield-check' : 'check-circle', 14)} ${isLandownerUser() ? 'Mark Complete' : 'Complete Quest'}
                       </button>`
                }
            </div>
        </div>`;
    }

    // ─── Section Renderer ─────────────────────────────────────────────────────

    function renderSchedules() {
        if (schedulesList.length === 0) {
            schedulesContainer.innerHTML = `
                <div class="task-empty-state">
                    ${lucideIcon('sprout', 48)}
                    <h3>No quests yet!</h3>
                    <p>Click <strong>+ Add Task</strong> to plant your first quest.</p>
                </div>`;
            lucide.createIcons();
            return;
        }

        // Group: active first, completed last
        const active    = schedulesList.filter(s => s.status !== 'completed');
        const completed = schedulesList.filter(s => s.status === 'completed');

        let html = '';

        if (active.length) {
            html += `<div class="task-section-label">
                        ${lucideIcon('swords', 15)}
                        Active Quests <span class="task-count-badge">${active.length}</span>
                     </div>
                     <div class="task-quest-grid">${active.map(renderCard).join('')}</div>`;
        }

        if (completed.length) {
            html += `<div class="task-section-label task-section-label--done" style="margin-top:2rem;">
                        ${lucideIcon('check-circle-2', 15)}
                        Completed <span class="task-count-badge task-count-badge--done">${completed.length}</span>
                     </div>
                     <div class="task-quest-grid task-quest-grid--done">${completed.map(renderCard).join('')}</div>`;
        }

        schedulesContainer.innerHTML = html;

        // Activate lucide icons
        lucide.createIcons();
    }

    // ─── Load Schedules ───────────────────────────────────────────────────────

    async function loadSchedules() {
        try {
            const response = await fetch('../api/schedules.php');
            const res = await response.json();
            if (res.status === 'success' && res.data.schedules.length > 0) {
                schedulesList = res.data.schedules;
            } else {
                // Use mock data when API returns nothing
                schedulesList = MOCK_SCHEDULES;
            }
        } catch (err) {
            console.warn("API unavailable, using mock data:", err);
            schedulesList = MOCK_SCHEDULES;
        }
        renderSchedules();
    }

    // ─── Add Task Form ────────────────────────────────────────────────────────

    if (addTaskForm) {
        addTaskForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            const payload = {
                action:      'create_schedule',
                title:       document.getElementById('title').value,
                task_type:   document.getElementById('task_type').value,
                start_time:  document.getElementById('start_time').value,
                end_time:    document.getElementById('end_time').value,
                description: document.getElementById('description').value
            };

            try {
                const response = await fetch('../api/schedules.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const res = await response.json();
                if (res.status === 'success') {
                    const modal = bootstrap.Modal.getInstance(addTaskModalEl);
                    if (modal) modal.hide();
                    addTaskForm.reset();
                    loadSchedules();
                } else {
                    alert("Error: " + res.message);
                }
            } catch (err) {
                console.error("Schedule task failed:", err);
            }
        });
    }

    function isLandownerUser() {
        return (typeof window.CURRENT_USER_ROLE !== 'undefined' && window.CURRENT_USER_ROLE === 'landowner')
            || window.location.pathname.includes('/landowner/');
    }

    // ─── Modal & Quest Completion Handlers ────────────────────────────────────

    let currentSelectedProofImage = '';
    let currentSelectedProofFile = null;

    function ensureQuestModals() {
        if (document.getElementById('completeQuestModal')) return;

        const modalHtml = `
        <!-- Complete Quest Modal -->
        <div class="modal fade" id="completeQuestModal" tabindex="-1" aria-hidden="true" style="z-index: 10050;">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content drive-modal-content border-0 shadow-lg" style="border-radius: 24px;">
                    <div class="modal-header border-0 pb-1">
                        <div>
                            <span class="badge rounded-pill px-2.5 py-1 mb-1 fw-bold text-xs" id="complete_task_role_badge">GARDENER PROOF</span>
                            <h5 class="modal-title fw-bold text-dark m-0" id="complete_modal_heading">Complete Quest</h5>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body pt-2 pb-3">
                        <!-- Quest Summary Banner -->
                        <div class="p-3 rounded-4 mb-3" style="background:#F7EFE6;border:1.5px solid #DFCFC2;">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="fw-bold text-dark small" id="complete_land_title">Plot A</span>
                                <span class="badge rounded-pill" style="background:#FEE7AA;color:#4A3528;border:1px solid #785D4D;font-weight:800;" id="complete_xp_badge">+100 XP</span>
                            </div>
                            <h6 class="fw-bold text-dark mb-1" id="complete_task_title">Task Title</h6>
                            <p class="text-muted mb-0 small" id="complete_task_desc">Task instructions...</p>
                        </div>

                        <form id="completeQuestForm">
                            <input type="hidden" id="complete_quest_id" name="id">
                            <input type="hidden" id="complete_role" name="role" value="gardener">

                            <!-- Gardener Section (Proof Photo Strictly Required - Only Gardeners Upload) -->
                            <div id="gardenerProofSection">
                                <div class="alert alert-info py-2 px-3 rounded-3 border-0 d-flex align-items-center gap-2 mb-3" style="font-size:0.78rem;">
                                    <i class="bi bi-camera-fill fs-6 text-primary flex-shrink-0"></i>
                                    <span><strong>Photo Proof Required:</strong> As the gardener, attach a photo of your completed garden work to earn your XP and finish this quest.</span>
                                </div>

                                <label class="form-label text-secondary d-block mb-1" style="font-size:0.75rem; font-weight:700;">WORK PROOF PHOTO <span class="text-danger">*</span></label>
                                
                                <!-- Drag & Drop / File Input Box -->
                                <div class="quest-dropzone p-3 text-center rounded-4 mb-2" id="questDropzone" style="border:2px dashed #C4B5A5;background:#FFFDF9;cursor:pointer;">
                                    <input type="file" id="proofFileInput" accept="image/*" class="d-none">
                                    <div id="dropzonePrompt">
                                        <i class="bi bi-cloud-arrow-up fs-2 text-secondary d-block mb-1"></i>
                                        <span class="fw-bold small text-dark d-block">Click to upload photo of completed work</span>
                                        <span class="text-muted" style="font-size:0.72rem;">JPG, PNG, WebP up to 10MB</span>
                                    </div>
                                    <div id="proofPreviewWrap" class="d-none position-relative mt-1">
                                        <img id="proofPreviewImg" class="rounded-3 shadow-xs object-fit-cover w-100" style="max-height:180px;" src="" alt="Proof Preview">
                                        <button type="button" class="btn btn-sm btn-danger rounded-circle position-absolute top-0 end-0 m-1" id="clearProofBtn" title="Remove photo" style="width:26px;height:26px;padding:0;">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Quick Sample Photos -->
                                <div class="mb-3">
                                    <span class="text-muted d-block mb-1.5" style="font-size:0.7rem;font-weight:600;">OR USE SAMPLE WORK PHOTO:</span>
                                    <div class="d-flex flex-wrap gap-1.5">
                                        <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill py-1 px-2.5 small" onclick="useSamplePhoto('watering')" style="font-size:0.7rem;">
                                            <i class="bi bi-droplet text-primary me-1"></i> Watered Beds
                                        </button>
                                        <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill py-1 px-2.5 small" onclick="useSamplePhoto('planting')" style="font-size:0.7rem;">
                                            <i class="bi bi-seedling text-success me-1"></i> Planted Beds
                                        </button>
                                        <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill py-1 px-2.5 small" onclick="useSamplePhoto('harvesting')" style="font-size:0.7rem;">
                                            <i class="bi bi-basket text-danger me-1"></i> Harvest Crop
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Landowner Section (Direct Mark Complete - No Photo Upload) -->
                            <div id="landownerProofSection" class="d-none">
                                <div class="alert alert-success py-2.5 px-3 rounded-3 border-0 d-flex align-items-center gap-2 mb-3" style="font-size:0.8rem;">
                                    <i class="bi bi-shield-check fs-5 text-success flex-shrink-0"></i>
                                    <div>
                                        <strong>Landowner Direct Verification:</strong><br>
                                        You are marking this quest completed for <strong id="landownerTargetGardener">the gardener</strong> directly. Only gardeners upload proof photos.
                                    </div>
                                </div>
                            </div>

                            <!-- Completion Notes -->
                            <div class="mb-3">
                                <label for="completeNotes" class="form-label text-secondary" style="font-size:0.75rem; font-weight:700;" id="completeNotesLabel">COMPLETION NOTES (OPTIONAL)</label>
                                <textarea class="form-control drive-form-control w-100" id="completeNotes" rows="2" placeholder="e.g., Checked all tomato beds, watered thoroughly."></textarea>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex justify-content-end gap-2 pt-2">
                                <button type="button" class="btn btn-light rounded-pill px-3.5" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" id="submitCompleteQuestBtn" class="btn btn-success rounded-pill px-4 fw-bold">
                                    <i class="bi bi-check-circle-fill me-1"></i> Complete Quest
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- View Quest Details & Proof Modal -->
        <div class="modal fade" id="viewQuestModal" tabindex="-1" aria-hidden="true" style="z-index: 10050;">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content drive-modal-content border-0 shadow-lg" style="border-radius: 24px;">
                    <div class="modal-header border-0 pb-1">
                        <div>
                            <span class="badge rounded-pill px-2.5 py-1 mb-1 fw-bold text-xs" style="background:#DCFCE7;color:#15803D;border:1.5px solid #785D4D;" id="view_status_pill">
                                <i class="bi bi-check-circle-fill me-1"></i> Completed
                            </span>
                            <h5 class="modal-title fw-bold text-dark m-0" id="view_quest_title">Quest Details</h5>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body pt-2">
                        <!-- Location & Gardener Banner -->
                        <div class="p-3 rounded-4 mb-3" style="background:#F7EFE6;border:1.5px solid #DFCFC2;">
                            <div class="d-flex align-items-center justify-content-between mb-1.5">
                                <span class="fw-bold text-dark" id="view_land_title">Plot A</span>
                                <span class="badge rounded-pill" style="background:#FEE7AA;color:#4A3528;border:1px solid #785D4D;font-weight:800;" id="view_xp_badge">+100 XP</span>
                            </div>
                            <div class="d-flex align-items-center gap-2 text-muted small mb-1">
                                <i class="bi bi-person-fill text-secondary"></i> <span id="view_gardener_name">Gardener</span>
                                <span>•</span>
                                <i class="bi bi-clock text-secondary"></i> <span id="view_time_label">Time</span>
                            </div>
                            <p class="text-secondary mb-0 small mt-2" id="view_task_desc">Description...</p>
                        </div>

                        <!-- Completion Verification Info -->
                        <div class="p-3 rounded-4 mb-3 bg-light border">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="bi bi-patch-check-fill text-success fs-5"></i>
                                <div>
                                    <div class="fw-bold text-dark small" id="view_completed_by">Completed by Mary Gardener</div>
                                    <div class="text-muted" style="font-size:0.72rem;" id="view_completed_at">Completed on Oct 4, 2026</div>
                                </div>
                            </div>
                            <div id="view_completion_notes_wrap" class="mt-2 pt-2 border-top d-none">
                                <span class="text-secondary d-block fw-bold" style="font-size:0.7rem;">NOTES:</span>
                                <p class="mb-0 text-dark small" id="view_completion_notes"></p>
                            </div>
                        </div>

                        <!-- Proof Photo Display Container -->
                        <div id="view_proof_photo_wrap" class="mb-3 d-none">
                            <label class="form-label text-secondary fw-bold small d-block mb-1.5">
                                <i class="bi bi-image text-success me-1"></i> COMPLETION PROOF PHOTO
                            </label>
                            <div class="rounded-4 overflow-hidden border border-2 text-center" style="background:#FAF7F2;border-color:#DFCFC2 !important;">
                                <img id="view_proof_photo_img" src="" class="w-100 object-fit-contain" style="max-height: 260px;" alt="Completion Proof Photo">
                            </div>
                        </div>
                        <div id="view_no_photo_notice" class="alert alert-light border small text-muted d-none">
                            <i class="bi bi-shield-check text-success me-1"></i> Marked as completed directly by Landowner without photo attachment.
                        </div>

                        <div class="d-flex justify-content-end pt-2">
                            <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Toast Feedback Container -->
        <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 10600;">
            <div id="questToast" class="toast align-items-center text-white bg-dark border-0 rounded-4 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body py-2.5 px-3 d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill text-success" id="questToastIcon"></i>
                        <span id="questToastMsg" class="small fw-medium">Quest completed!</span>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHtml);

        const dropzone = document.getElementById('questDropzone');
        const fileInput = document.getElementById('proofFileInput');
        const clearBtn = document.getElementById('clearProofBtn');
        const form = document.getElementById('completeQuestForm');

        if (dropzone && fileInput) {
            dropzone.addEventListener('click', function (e) {
                if (e.target.closest('#clearProofBtn')) return;
                fileInput.click();
            });

            fileInput.addEventListener('change', function () {
                if (fileInput.files && fileInput.files[0]) {
                    const file = fileInput.files[0];
                    currentSelectedProofFile = file;
                    const reader = new FileReader();
                    reader.onload = function (evt) {
                        currentSelectedProofImage = evt.target.result;
                        showProofPreview(currentSelectedProofImage);
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        if (clearBtn) {
            clearBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                clearProofPhoto();
            });
        }

        if (form) {
            form.addEventListener('submit', handleCompleteQuestSubmit);
        }
    }

    function showProofPreview(src) {
        const previewWrap = document.getElementById('proofPreviewWrap');
        const promptWrap = document.getElementById('dropzonePrompt');
        const img = document.getElementById('proofPreviewImg');
        if (img) img.src = src;
        if (previewWrap) previewWrap.classList.remove('d-none');
        if (promptWrap) promptWrap.classList.add('d-none');

        // Enable submit button for gardener once proof photo is attached
        const submitBtn = document.getElementById('submitCompleteQuestBtn');
        if (submitBtn) submitBtn.disabled = false;
    }

    function clearProofPhoto() {
        currentSelectedProofImage = '';
        currentSelectedProofFile = null;
        const fileInput = document.getElementById('proofFileInput');
        if (fileInput) fileInput.value = '';
        const previewWrap = document.getElementById('proofPreviewWrap');
        const promptWrap = document.getElementById('dropzonePrompt');
        if (previewWrap) previewWrap.classList.add('d-none');
        if (promptWrap) promptWrap.classList.remove('d-none');

        // Disable submit button if gardener role
        if (!isLandownerUser()) {
            const submitBtn = document.getElementById('submitCompleteQuestBtn');
            if (submitBtn) submitBtn.disabled = true;
        }
    }

    window.useSamplePhoto = function (type) {
        const sampleMap = {
            watering: '../assets/images/tasks/watering.png',
            planting: '../assets/images/tasks/planting.png',
            harvesting: '../assets/images/tasks/harvesting.png',
            weeding: '../assets/images/tasks/weeding.png'
        };
        const sampleUrl = sampleMap[type] || sampleMap.watering;
        currentSelectedProofImage = sampleUrl;
        currentSelectedProofFile = null;
        showProofPreview(sampleUrl);
    };

    window.openCompleteQuestModal = function (id) {
        ensureQuestModals();
        const sched = schedulesList.find(s => s.id === id);
        if (!sched) return;

        clearProofPhoto();
        document.getElementById('complete_quest_id').value = id;
        document.getElementById('complete_task_title').textContent = sched.title;
        document.getElementById('complete_land_title').textContent = `${sched.land_title} • Assigned to: ${sched.gardener || 'Unassigned'}`;
        document.getElementById('complete_xp_badge').textContent = `+${sched.xp || 100} XP`;
        document.getElementById('complete_task_desc').textContent = sched.description || 'Complete the scheduled garden activities.';
        document.getElementById('completeNotes').value = '';

        const isLandowner = isLandownerUser();
        const roleInput = document.getElementById('complete_role');
        const roleBadge = document.getElementById('complete_task_role_badge');
        const gardenerSection = document.getElementById('gardenerProofSection');
        const landownerSection = document.getElementById('landownerProofSection');
        const submitBtn = document.getElementById('submitCompleteQuestBtn');
        const notesLabel = document.getElementById('completeNotesLabel');
        const notesInput = document.getElementById('completeNotes');

        if (roleInput) roleInput.value = isLandowner ? 'landowner' : 'gardener';

        if (isLandowner) {
            // Landowner marks quest complete directly - only gardeners upload photos
            if (roleBadge) {
                roleBadge.textContent = 'LANDOWNER VERIFICATION';
                roleBadge.className = 'badge bg-success-subtle text-success rounded-pill px-2.5 py-1 mb-1 fw-bold text-xs';
            }
            document.getElementById('complete_modal_heading').textContent = 'Mark Quest as Completed';
            document.getElementById('landownerTargetGardener').textContent = sched.gardener || 'the assigned gardener';
            if (gardenerSection) gardenerSection.classList.add('d-none');
            if (landownerSection) landownerSection.classList.remove('d-none');
            if (notesLabel) notesLabel.textContent = 'VERIFICATION NOTES (OPTIONAL)';
            if (notesInput) notesInput.placeholder = 'e.g., Inspected garden plot and verified work completed.';
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-shield-check me-1"></i> Mark as Completed';
            }
        } else {
            // Gardener MUST attach a photo proof
            if (roleBadge) {
                roleBadge.textContent = 'GARDENER PROOF REQUIRED';
                roleBadge.className = 'badge bg-warning-subtle text-warning-emphasis rounded-pill px-2.5 py-1 mb-1 fw-bold text-xs';
            }
            document.getElementById('complete_modal_heading').textContent = 'Complete Quest & Submit Proof';
            if (gardenerSection) gardenerSection.classList.remove('d-none');
            if (landownerSection) landownerSection.classList.add('d-none');
            if (notesLabel) notesLabel.textContent = 'COMPLETION NOTES (OPTIONAL)';
            if (notesInput) notesInput.placeholder = 'e.g., Watered all beds thoroughly, weeded rows 1 to 4.';
            if (submitBtn) {
                submitBtn.disabled = true; // Disabled until photo is provided
                submitBtn.innerHTML = '<i class="bi bi-camera me-1"></i> Submit Proof & Complete';
            }
        }

        const modalEl = document.getElementById('completeQuestModal');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    };

    window.openViewQuestModal = function (id) {
        ensureQuestModals();
        const sched = schedulesList.find(s => s.id === id);
        if (!sched) return;

        document.getElementById('view_quest_title').textContent = sched.title;
        document.getElementById('view_land_title').textContent = sched.land_title;
        document.getElementById('view_xp_badge').textContent = `+${sched.xp || 100} XP`;
        document.getElementById('view_gardener_name').textContent = sched.gardener || 'Unassigned';
        document.getElementById('view_time_label').textContent = `${formatDate(sched.start_time)}`;
        document.getElementById('view_task_desc').textContent = sched.description || 'No description.';

        const completedByEl = document.getElementById('view_completed_by');
        const completedAtEl = document.getElementById('view_completed_at');
        if (completedByEl) {
            completedByEl.textContent = `Completed by ${sched.completed_by || sched.gardener || 'Gardener'}`;
        }
        if (completedAtEl) {
            completedAtEl.textContent = sched.completed_at ? `Verified on ${sched.completed_at}` : 'Verified successfully';
        }

        const notesWrap = document.getElementById('view_completion_notes_wrap');
        const notesEl = document.getElementById('view_completion_notes');
        if (sched.completion_notes) {
            notesWrap.classList.remove('d-none');
            notesEl.textContent = sched.completion_notes;
        } else {
            notesWrap.classList.add('d-none');
        }

        const photoWrap = document.getElementById('view_proof_photo_wrap');
        const photoImg = document.getElementById('view_proof_photo_img');
        const noPhotoNotice = document.getElementById('view_no_photo_notice');

        if (sched.proof_image) {
            photoWrap.classList.remove('d-none');
            noPhotoNotice.classList.add('d-none');
            const imgPath = (sched.proof_image.startsWith('http') || sched.proof_image.startsWith('data:') || sched.proof_image.startsWith('../'))
                ? sched.proof_image
                : '../' + sched.proof_image;
            photoImg.src = imgPath;
        } else {
            photoWrap.classList.add('d-none');
            noPhotoNotice.classList.remove('d-none');
        }

        const modalEl = document.getElementById('viewQuestModal');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    };

    async function handleCompleteQuestSubmit(e) {
        e.preventDefault();
        const id = parseInt(document.getElementById('complete_quest_id').value);
        const role = document.getElementById('complete_role').value;
        const notes = document.getElementById('completeNotes').value;

        if (role === 'landowner') {
            currentSelectedProofFile = null;
            currentSelectedProofImage = '';
        } else if (role === 'gardener' && !currentSelectedProofImage && !currentSelectedProofFile) {
            alert("A photo proof is required for gardeners to complete this quest.");
            return;
        }

        const submitBtn = document.getElementById('submitCompleteQuestBtn');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Completing...';
        }

        try {
            let res;
            if (currentSelectedProofFile) {
                const formData = new FormData();
                formData.append('action', 'complete_schedule');
                formData.append('id', id);
                formData.append('role', role);
                formData.append('notes', notes);
                formData.append('proof_image', currentSelectedProofFile);

                const response = await fetch('../api/schedules.php', {
                    method: 'POST',
                    body: formData
                });
                res = await response.json();
            } else {
                const payload = {
                    action: 'complete_schedule',
                    id: id,
                    role: role,
                    notes: notes,
                    proof_image: currentSelectedProofImage || ''
                };
                const response = await fetch('../api/schedules.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                res = await response.json();
            }

            if (res.status === 'success') {
                const modalEl = document.getElementById('completeQuestModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();

                showQuestToast(res.message || "Quest completed successfully!");
                await loadSchedules();
            } else {
                alert("Error: " + (res.message || "Failed to complete quest."));
            }
        } catch (err) {
            console.error("Complete quest failed:", err);
            alert("Connection error. Could not complete quest.");
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> Complete Quest';
            }
        }
    }

    function showQuestToast(msg) {
        const toastEl = document.getElementById('questToast');
        const msgEl = document.getElementById('questToastMsg');
        if (!toastEl) return;
        if (msgEl) msgEl.textContent = msg;
        const toast = new bootstrap.Toast(toastEl, { delay: 3500 });
        toast.show();
    }

    // ─── Initial Load ─────────────────────────────────────────────────────────
    ensureQuestModals();
    loadSchedules();
});
