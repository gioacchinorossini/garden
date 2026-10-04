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
        const type   = TYPE_CONFIG[sched.task_type]   || TYPE_CONFIG.watering;
        const status = STATUS_CONFIG[sched.status]    || STATUS_CONFIG.pending;
        const diff   = DIFFICULTY_CONFIG[sched.difficulty] || DIFFICULTY_CONFIG.easy;
        const xp     = sched.xp || 100;
        const heartsFilled = diff.stars || 1;

        // Custom inline icon
        const iconContent = taskIcon(sched.task_type);

        // Status pill
        const statusPill = `
            <span class="task-status-pill" style="background:${status.bg};color:${status.color};">
                <span style="width:6px;height:6px;border-radius:50%;background:${status.dot};display:inline-block;flex-shrink:0;"></span>
                ${status.label}
            </span>`;

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

        // Completed overlay ribbon
        const completedRibbon = sched.status === 'completed'
            ? `<div class="task-completed-ribbon">
                    ${lucideIcon('check-circle-2', 15)}
                    Completed
               </div>`
            : '';

        return `
        <div class="task-quest-card ${sched.status === 'completed' ? 'task-quest-card--done' : ''}"
             style="--card-color:${type.color};--card-accent-bg:${type.accentBg};--card-accent-border:${type.accentBorder};">

            ${completedRibbon}

            <!-- Card Header Band (Cats & Soup Aesthetic) -->
            <div class="task-card-header-band">
                <!-- Squircle Avatar Container -->
                <div class="task-icon-bubble" title="${type.label}">
                    ${iconContent}
                </div>

                <!-- Secondary Circular Crop / Seed Slot (Matching reference image) -->
                <div class="cats-slot-circle d-none d-sm-inline-flex" title="${type.label} Slot" style="width:36px;height:36px;">
                    <i class="bi bi-stars" style="font-size:13px;color:#DDA15E;"></i>
                </div>

                <!-- Title & Heart Meter -->
                <div class="task-card-title-block">
                    <div class="d-flex align-items-center flex-wrap gap-1 mb-0.5">
                        <span class="task-type-label">${type.label}</span>
                        ${catsAndSoupHearts(heartsFilled, 6)}
                    </div>
                    <h3 class="task-card-title">${sched.title}</h3>
                </div>

                <!-- 3 Circular Equipment / Tool Slots on Right (Cats & Soup Exact Feature) -->
                <div class="d-none d-md-flex align-items-center gap-1.5 ms-auto flex-shrink-0">
                    <div class="cats-slot-circle" title="Tool: Shears / Can">
                        <i class="bi bi-scissors text-secondary" style="font-size:12px;opacity:0.75;"></i>
                    </div>
                    <div class="cats-slot-circle" title="Gear: Straw Hat">
                        <i class="bi bi-sun text-secondary" style="font-size:12px;opacity:0.75;"></i>
                    </div>
                    <div class="cats-slot-circle" title="Gear: Gloves">
                        <i class="bi bi-hand-index-thumb text-secondary" style="font-size:12px;opacity:0.75;"></i>
                    </div>
                </div>

                <!-- Badges -->
                <div class="task-card-badges ms-2">
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

            <!-- Footer -->
            <div class="task-card-footer">
                <div class="task-difficulty-block">
                    ${difficultyStars(sched.difficulty)}
                    <span class="task-difficulty-label" style="color:${diff.color};">${diff.label}</span>
                </div>
                <button class="task-action-btn" data-id="${sched.id}">
                    ${sched.status === 'completed' ? lucideIcon('eye', 14) + ' View' : lucideIcon('play', 14) + ' Start Quest'}
                </button>
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

    // ─── Initial Load ─────────────────────────────────────────────────────────
    loadSchedules();
});
