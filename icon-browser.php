<?php
$base_path = './';

// 10 Task Icons extracted from tasksicons.png
$taskIcons = [
    'watering'    => ['name' => 'Watering',           'file' => 'watering.png',    'desc' => 'Irrigation, sprinkler, hand-watering'],
    'planting'    => ['name' => 'Planting',           'file' => 'planting.png',    'desc' => 'Seedling transplant, sowing, garden bed'],
    'weeding'     => ['name' => 'Weeding',            'file' => 'weeding.png',     'desc' => 'Weed removal, invasive plant clearing'],
    'pruning'     => ['name' => 'Pruning',            'file' => 'pruning.png',     'desc' => 'Shears, trimming branches, deadheading'],
    'mulching'    => ['name' => 'Mulching',           'file' => 'mulching.png',    'desc' => 'Woodchip and straw mulch application'],
    'fertilizing' => ['name' => 'Fertilizing',        'file' => 'fertilizing.png', 'desc' => 'Compost, organic fertilizer, soil feeding'],
    'trellising'  => ['name' => 'Trellising',         'file' => 'trellising.png',  'desc' => 'Lattice, stakes, climbing vine support'],
    'harvesting'  => ['name' => 'Harvesting',         'file' => 'harvesting.png',  'desc' => 'Produce gathering, vegetable harvest'],
    'pests'       => ['name' => 'Checking for Pests', 'file' => 'pests.png',       'desc' => 'Inspection, ladybugs, pest scouting'],
    'cleaning'    => ['name' => 'Cleaning Tools',     'file' => 'cleaning.png',    'desc' => 'Tool sanitization, sharpening, storage'],
];

$cropIconsDir = __DIR__ . '/assets/crop-icons';
$cropIcons = [];
if (is_dir($cropIconsDir)) {
    $folders = array_diff(scandir($cropIconsDir), ['.', '..', '.keep']);
    sort($folders);
    foreach ($folders as $folder) {
        $svgPath = $cropIconsDir . '/' . $folder . '/' . $folder . '.svg';
        if (file_exists($svgPath)) {
            $cropIcons[] = $folder;
        }
    }
}

$lucideIcons = [
    'activity','alert-circle','alert-triangle','archive','arrow-down','arrow-left',
    'arrow-right','arrow-up','award','bar-chart','bar-chart-2','bell','bell-off',
    'book','book-open','bookmark','box','calendar','camera','check','check-circle',
    'check-circle-2','check-square','chevron-down','chevron-left','chevron-right',
    'chevron-up','circle','clipboard','clock','clock-3','cloud','cloud-rain',
    'code','columns','compass','copy','crop','database','delete','download',
    'droplets','droplet','edit','edit-2','edit-3','eye','eye-off','file',
    'file-text','filter','flag','flask-conical','folder','git-branch','globe',
    'grid','heart','help-circle','home','image','inbox','info','layers',
    'leaf','link','list','loader','lock','log-out','mail','map',
    'map-pin','maximize','menu','message-circle','message-square','minus',
    'monitor','moon','more-horizontal','more-vertical','move',
    'package','paperclip','pause','phone','play','play-circle','plus',
    'plus-circle','power','printer','refresh-cw','save','scissors',
    'search','send','settings','share','share-2','shield','skip-back',
    'skip-forward','sliders','smartphone','sprout','star','sun','swords',
    'tag','thermometer','thumbs-up','thumbs-down','toggle-left','toggle-right',
    'tool','trash','trash-2','tree-pine','trending-up','truck','tv','type',
    'unlock','upload','user','user-round','user-round-x','user-check',
    'user-plus','users','video','volume','volume-2','watch','wheat',
    'wifi','wind','x','x-circle','zap','zoom-in','zoom-out',
];
sort($lucideIcons);

$bootstrapIcons = [
    'alarm','archive','arrow-down','arrow-left','arrow-right','arrow-up',
    'award','bag','bar-chart','bar-chart-fill','basket3-fill','bell','bell-fill',
    'bookmark','bookmark-fill','box','briefcase','calendar','calendar-check',
    'calendar-event','camera','check','check-circle','check-circle-fill',
    'check-lg','check-square','chevron-down','chevron-left','chevron-right',
    'chevron-up','circle','clipboard','clock','cloud','cloud-rain','code',
    'collection','compass','copy','credit-card','cursor','diagram-3',
    'display','door-open','download','droplet','droplet-fill','droplet-half',
    'envelope','envelope-fill','exclamation','exclamation-circle',
    'exclamation-triangle','eye','eye-fill','eye-slash','file-earmark',
    'file-earmark-text','filter','flag','folder','folder-fill','fullscreen',
    'gear','geo','geo-alt','geo-alt-fill','globe','graph-up','grid',
    'grid-fill','heart','heart-fill','house','house-fill','image',
    'inbox','info','info-circle','journal','key','layers','layout-text-window',
    'leaf','link','list','list-check','list-ul','lock','lock-fill',
    'map','menu-button','mic','moon','newspaper','node-plus',
    'palette','patch-check','pause','people','people-fill','person',
    'person-fill','phone','pin','play','plus','plus-circle','plus-circle-fill',
    'plus-lg','printer','question','question-circle','recycle','reply',
    'scissors','search','send','shield','shield-check','shuffle',
    'skip-end','skip-start','sliders','star','star-fill','sun',
    'tag','three-dots','trash','trash-fill','trophy','tv',
    'upload','upc-scan','vector-pen','watch','wifi','x',
    'x-circle','x-lg','zoom-in','zoom-out',
];
sort($bootstrapIcons);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Icon Browser - Garden System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $base_path ?>assets/vendor/bootstrap-icons/bootstrap-icons.css">
    <style>
        :root {
            --bg:#0f1117;--surface:#1a1d27;--surface2:#22263a;
            --border:rgba(255,255,255,0.07);--primary:#4ade80;--primary2:#22c55e;
            --text:#e8eaf0;--sub:#8b90a7;--muted:#545870;
            --radius:14px;--font:'Outfit',sans-serif;
        }
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
        body{background:var(--bg);color:var(--text);font-family:var(--font);min-height:100vh;}
        .ib-header{position:sticky;top:0;z-index:100;background:rgba(15,17,23,.88);
            backdrop-filter:blur(18px);border-bottom:1px solid var(--border);
            padding:1rem 1.5rem;display:flex;align-items:center;gap:1rem;flex-wrap:wrap;}
        .ib-logo{display:flex;align-items:center;gap:.6rem;font-weight:800;font-size:1.1rem;white-space:nowrap;}
        .ib-logo-dot{width:10px;height:10px;border-radius:50%;background:var(--primary);}
        .ib-search{flex:1;min-width:200px;background:var(--surface2);border:1px solid var(--border);
            border-radius:99px;padding:.5rem 1rem .5rem 2.6rem;color:var(--text);
            font-family:var(--font);font-size:.88rem;outline:none;transition:border-color .15s;
            background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%238b90a7' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cpath d='m21 21-4.35-4.35'/%3E%3C/svg%3E");
            background-repeat:no-repeat;background-position:.75rem center;}
        .ib-search:focus{border-color:var(--primary);}
        .ib-search::placeholder{color:var(--muted);}
        .ib-count{font-size:.78rem;color:var(--sub);white-space:nowrap;}
        .ib-count strong{color:var(--primary);}
        .ib-tabs{display:flex;gap:.5rem;padding:.85rem 1.5rem 0;border-bottom:1px solid var(--border);overflow-x:auto;scrollbar-width:none;}
        .ib-tabs::-webkit-scrollbar{display:none;}
        .ib-tab{display:flex;align-items:center;gap:.4rem;padding:.45rem 1rem;
            border-radius:8px 8px 0 0;font-size:.82rem;font-weight:600;cursor:pointer;
            border:none;background:transparent;color:var(--sub);transition:color .15s,background .15s;
            white-space:nowrap;font-family:var(--font);}
        .ib-tab:hover{color:var(--text);background:var(--surface2);}
        .ib-tab.active{color:var(--primary);background:var(--surface);border-bottom:2px solid var(--primary);}
        .ib-tab-badge{background:var(--surface2);border-radius:99px;padding:1px 7px;font-size:.65rem;font-weight:700;}
        .ib-tab.active .ib-tab-badge{background:rgba(74,222,128,.15);color:var(--primary);}
        .ib-main{padding:1.5rem;}
        .ib-panel{display:none;} .ib-panel.active{display:block;}
        .ib-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(100px,1fr));gap:.6rem;}
        .ib-grid-tasks{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:.85rem;}
        .ib-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);
            padding:1rem .5rem .7rem;display:flex;flex-direction:column;align-items:center;
            gap:.5rem;cursor:pointer;transition:transform .15s cubic-bezier(.34,1.56,.64,1),
            border-color .15s,background .15s;position:relative;overflow:hidden;}
        .ib-card:hover{transform:translateY(-3px) scale(1.04);border-color:var(--primary);background:var(--surface2);}
        .ib-card:active{transform:scale(.97);}
        .ib-card img{width:44px;height:44px;object-fit:contain;}
        .ib-card-task img{width:64px;height:64px;object-fit:contain;filter:drop-shadow(0 4px 8px rgba(0,0,0,0.3));}
        .ib-card .bi{font-size:1.75rem;color:var(--text);transition:color .15s;}
        .ib-card:hover .bi{color:var(--primary);}
        .ib-card [data-lucide]{width:32px;height:32px;stroke:var(--text);stroke-width:1.75;transition:stroke .15s;}
        .ib-card:hover [data-lucide]{stroke:var(--primary);}
        .ib-card-name{font-size:.6rem;color:var(--sub);text-align:center;line-height:1.3;
            word-break:break-word;max-width:90px;}
        .ib-card-task .ib-card-name{font-size:.78rem;font-weight:600;color:var(--text);max-width:130px;}
        .ib-card-sub{font-size:.65rem;color:var(--muted);text-align:center;line-height:1.2;margin-top:-2px;}
        .ib-card:hover .ib-card-name{color:var(--text);}
        .ib-card.copied::after{content:'Copied!';position:absolute;inset:0;
            background:rgba(74,222,128,.18);display:flex;align-items:center;
            justify-content:center;font-size:.7rem;font-weight:700;color:var(--primary);
            border-radius:var(--radius);}
        .ib-empty{display:flex;flex-direction:column;align-items:center;justify-content:center;
            padding:5rem 2rem;color:var(--muted);gap:.75rem;font-size:.88rem;}
        .ib-empty svg{opacity:.3;}
        .ib-toast{position:fixed;bottom:1.5rem;left:50%;transform:translateX(-50%) translateY(80px);
            background:var(--surface2);border:1px solid var(--primary);border-radius:99px;
            padding:.5rem 1.25rem;font-size:.8rem;font-weight:600;color:var(--primary);
            white-space:nowrap;transition:transform .25s cubic-bezier(.34,1.56,.64,1),opacity .25s;
            opacity:0;z-index:999;pointer-events:none;}
        .ib-toast.show{transform:translateX(-50%) translateY(0);opacity:1;}
        .ib-section-title{font-size:.7rem;font-weight:700;letter-spacing:.8px;text-transform:uppercase;
            color:var(--sub);margin-bottom:1rem;}
        .ib-section-title code{font-size:.68rem;background:var(--surface2);padding:2px 6px;border-radius:5px;color:var(--primary);}
        @media(max-width:500px){
            .ib-grid{grid-template-columns:repeat(auto-fill,minmax(80px,1fr));}
            .ib-grid-tasks{grid-template-columns:repeat(auto-fill,minmax(110px,1fr));}
            .ib-header{padding:.75rem 1rem;}
            .ib-main{padding:1rem;}
        }
    </style>
</head>
<body>
<header class="ib-header">
    <div class="ib-logo">
        <div class="ib-logo-dot"></div>
        Icon Browser
    </div>
    <input class="ib-search" type="text" id="searchInput" placeholder="Search icons..." autocomplete="off">
    <div class="ib-count">Showing <strong id="visibleCount">0</strong> icons</div>
</header>

<div class="ib-tabs" id="tabBar">
    <button class="ib-tab active" data-tab="tasks">
        🌱 Task Icons <span class="ib-tab-badge"><?= count($taskIcons) ?></span>
    </button>
    <button class="ib-tab" data-tab="crop">
        🌾 Crop Icons <span class="ib-tab-badge"><?= count($cropIcons) ?></span>
    </button>
    <button class="ib-tab" data-tab="lucide">
        ✨ Lucide <span class="ib-tab-badge"><?= count($lucideIcons) ?></span>
    </button>
    <button class="ib-tab" data-tab="bootstrap">
        🎨 Bootstrap Icons <span class="ib-tab-badge"><?= count($bootstrapIcons) ?></span>
    </button>
</div>

<main class="ib-main">
    <!-- Task Icons (from tasksicons.png) -->
    <div class="ib-panel active" id="panel-tasks">
        <p class="ib-section-title">Illustrated Task Icons (from tasksicons.png) &mdash; <code>assets/images/tasks/{name}.png</code></p>
        <div class="ib-grid-tasks" id="grid-tasks">
            <?php foreach ($taskIcons as $key => $info): ?>
            <div class="ib-card ib-card-task" data-name="<?= htmlspecialchars($key . ' ' . $info['name'] . ' ' . $info['desc']) ?>" data-type="task" data-file="<?= htmlspecialchars($info['file']) ?>" title="<?= htmlspecialchars($info['name']) ?>">
                <img src="<?= $base_path ?>assets/images/tasks/<?= urlencode($info['file']) ?>"
                     alt="<?= htmlspecialchars($info['name']) ?>" loading="lazy">
                <span class="ib-card-name"><?= htmlspecialchars($info['name']) ?></span>
                <span class="ib-card-sub"><?= htmlspecialchars($info['desc']) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="ib-empty" id="empty-tasks" style="display:none;">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            No task icons match your search.
        </div>
    </div>

    <!-- Crop Icons -->
    <div class="ib-panel" id="panel-crop">
        <p class="ib-section-title">Crop &amp; Plant SVG Icons &mdash; <code>assets/crop-icons/{name}/{name}.svg</code></p>
        <div class="ib-grid" id="grid-crop">
            <?php foreach ($cropIcons as $icon): ?>
            <div class="ib-card" data-name="<?= htmlspecialchars($icon) ?>" data-type="crop" title="<?= htmlspecialchars($icon) ?>">
                <img src="<?= $base_path ?>assets/crop-icons/<?= urlencode($icon) ?>/<?= urlencode($icon) ?>.svg"
                     alt="<?= htmlspecialchars($icon) ?>" loading="lazy">
                <span class="ib-card-name"><?= htmlspecialchars(str_replace('-', ' ', $icon)) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="ib-empty" id="empty-crop" style="display:none;">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            No icons match your search.
        </div>
    </div>

    <!-- Lucide -->
    <div class="ib-panel" id="panel-lucide">
        <p class="ib-section-title">Lucide Icons &mdash; <code>data-lucide="{name}"</code></p>
        <div class="ib-grid" id="grid-lucide">
            <?php foreach ($lucideIcons as $icon): ?>
            <div class="ib-card" data-name="<?= htmlspecialchars($icon) ?>" data-type="lucide" title="<?= htmlspecialchars($icon) ?>">
                <i data-lucide="<?= htmlspecialchars($icon) ?>"></i>
                <span class="ib-card-name"><?= htmlspecialchars($icon) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="ib-empty" id="empty-lucide" style="display:none;">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            No icons match your search.
        </div>
    </div>

    <!-- Bootstrap Icons -->
    <div class="ib-panel" id="panel-bootstrap">
        <p class="ib-section-title">Bootstrap Icons &mdash; <code>class="bi bi-{name}"</code></p>
        <div class="ib-grid" id="grid-bootstrap">
            <?php foreach ($bootstrapIcons as $icon): ?>
            <div class="ib-card" data-name="<?= htmlspecialchars($icon) ?>" data-type="bootstrap" title="<?= htmlspecialchars($icon) ?>">
                <i class="bi bi-<?= htmlspecialchars($icon) ?>"></i>
                <span class="ib-card-name"><?= htmlspecialchars($icon) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="ib-empty" id="empty-bootstrap" style="display:none;">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            No icons match your search.
        </div>
    </div>
</main>

<div class="ib-toast" id="toast"></div>

<script src="<?= $base_path ?>assets/vendor/lucide/lucide.min.js"></script>
<script>
lucide.createIcons();

// Tab switching
const tabs = document.querySelectorAll('.ib-tab');
const panels = document.querySelectorAll('.ib-panel');
let activeTab = 'tasks';

tabs.forEach(tab => {
    tab.addEventListener('click', () => {
        tabs.forEach(t => t.classList.remove('active'));
        panels.forEach(p => p.classList.remove('active'));
        tab.classList.add('active');
        activeTab = tab.dataset.tab;
        document.getElementById('panel-' + activeTab).classList.add('active');
        filterIcons(document.getElementById('searchInput').value);
    });
});

// Search
const searchInput = document.getElementById('searchInput');
const visibleCount = document.getElementById('visibleCount');

function filterIcons(query) {
    const q = query.toLowerCase().trim();
    const grid = document.getElementById('grid-' + activeTab);
    const empty = document.getElementById('empty-' + activeTab);
    const cards = grid.querySelectorAll('.ib-card');
    let count = 0;
    cards.forEach(card => {
        const match = !q || card.dataset.name.includes(q);
        card.style.display = match ? '' : 'none';
        if (match) count++;
    });
    visibleCount.textContent = count;
    empty.style.display = count === 0 ? 'flex' : 'none';
}

searchInput.addEventListener('input', e => filterIcons(e.target.value));
filterIcons('');

// Copy to clipboard
const toast = document.getElementById('toast');
let toastTimer;

function showToast(msg) {
    toast.textContent = msg;
    toast.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => toast.classList.remove('show'), 2200);
}

document.querySelectorAll('.ib-card').forEach(card => {
    card.addEventListener('click', () => {
        const type = card.dataset.type;
        const name = card.dataset.name;
        let snippet = '';
        if (type === 'task') {
            const file = card.dataset.file;
            snippet = 'assets/images/tasks/' + file;
        } else if (type === 'crop') {
            snippet = 'assets/crop-icons/' + name + '/' + name + '.svg';
        } else if (type === 'lucide') {
            snippet = '<i data-lucide="' + name + '"></i>';
        } else {
            snippet = '<i class="bi bi-' + name + '"></i>';
        }
        navigator.clipboard.writeText(snippet).then(() => {
            card.classList.add('copied');
            setTimeout(() => card.classList.remove('copied'), 900);
            showToast('Copied: ' + snippet);
        });
    });
});
</script>
</body>
</html>
