</div> <!-- /main-layout -->
</div> <!-- /app-container -->

<?php
$bottom_role = isset($_SESSION['active_role']) ? $_SESSION['active_role'] : 'admin';
$bottom_page = basename($_SERVER['PHP_SELF']);

function get_dock_link_class($page_name, $bottom_page)
{
    $active = ($bottom_page == $page_name);
    $base_classes = "flex flex-col items-center text-decoration-none text-[10px] font-medium transition-all px-2.5 py-1 rounded-full gap-0.5 hover:text-drive-text-main hover:translate-y-[-2px] duration-150";
    return $active
        ? "$base_classes bg-drive-surface-active text-drive-primary-text font-semibold"
        : "$base_classes text-drive-text-sub hover:bg-drive-surface-hover";
}
?>
<!-- Floating Bottom Navigation Dock (Visible on all devices) -->
<div
    class="fixed bottom-4 md:bottom-8 left-1/2 -translate-x-1/2 bg-white/90 backdrop-blur-lg border border-drive-border rounded-full shadow-2xl px-4 py-2 z-50 flex items-center gap-1 md:gap-3 transition-all duration-300 max-w-[95vw] md:max-w-none block md:hidden">
    <?php
    $base = isset($base_path) ? $base_path : '';
    if ($bottom_role == 'admin'):
        ?>
        <a href="<?php echo $base; ?>admin/dashboard.php"
            class="<?php echo get_dock_link_class('dashboard.php', $bottom_page); ?>">
            <i class="bi bi-grid-1x2-fill text-lg"></i>
            <span class="hidden sm:inline">Dashboard</span>
        </a>
        <a href="<?php echo $base; ?>admin/users.php" class="<?php echo get_dock_link_class('users.php', $bottom_page); ?>">
            <i class="bi bi-people-fill text-lg"></i>
            <span class="hidden sm:inline">Users</span>
        </a>
        <!-- Action Trigger -->
        <button
            class="w-11 h-11 bg-drive-primary text-white rounded-full flex items-center justify-center shadow-md hover:scale-105 hover:bg-drive-primary-hover active:scale-95 transition-all text-xl flex-shrink-0"
            data-bs-toggle="modal" data-bs-target="#addUserModal" title="New User">
            <i class="bi bi-plus-lg"></i>
        </button>
        <a href="<?php echo $base; ?>admin/lands.php" class="<?php echo get_dock_link_class('lands.php', $bottom_page); ?>">
            <i class="bi bi-card-image text-lg"></i>
            <span class="hidden sm:inline">Lands</span>
        </a>
        <a href="<?php echo $base; ?>admin/reports.php"
            class="<?php echo get_dock_link_class('reports.php', $bottom_page); ?>">
            <i class="bi bi-bar-chart-line-fill text-lg"></i>
            <span class="hidden sm:inline">Reports</span>
        </a>
    <?php elseif ($bottom_role == 'landowner'): ?>
        <a href="<?php echo $base; ?>landowner/dashboard.php"
            class="<?php echo get_dock_link_class('dashboard.php', $bottom_page); ?>">
            <i class="bi bi-speedometer2 text-lg"></i>
            <span class="hidden sm:inline">Dashboard</span>
        </a>
        <a href="<?php echo $base; ?>landowner/lands.php"
            class="<?php echo get_dock_link_class('lands.php', $bottom_page); ?>">
            <i class="bi bi-tree-fill text-lg"></i>
            <span class="hidden sm:inline">My Lands</span>
        </a>
        <!-- Action Trigger -->
        <a href="<?php echo $base; ?>landowner/register.php"
            class="w-11 h-11 bg-drive-primary text-white rounded-full flex items-center justify-center shadow-md hover:scale-105 hover:bg-drive-primary-hover active:scale-95 transition-all text-xl flex-shrink-0 text-decoration-none"
            title="Register Land">
            <i class="bi bi-plus-lg"></i>
        </a>
        <a href="<?php echo $base; ?>landowner/requests.php"
            class="<?php echo get_dock_link_class('requests.php', $bottom_page); ?>">
            <i class="bi bi-file-earmark-person-fill text-lg"></i>
            <span class="hidden sm:inline">Requests</span>
        </a>
        <a href="<?php echo $base; ?>landowner/schedules.php"
            class="<?php echo get_dock_link_class('schedules.php', $bottom_page); ?>">
            <i class="bi bi-calendar-event-fill text-lg"></i>
            <span class="hidden sm:inline">Schedules</span>
        </a>
    <?php elseif ($bottom_role == 'gardener'): ?>
        <a href="<?php echo $base; ?>gardener/dashboard.php"
            class="<?php echo get_dock_link_class('dashboard.php', $bottom_page); ?>">
            <i class="bi bi-house-door-fill text-lg"></i>
            <span class="hidden sm:inline">Overview</span>
        </a>
        <a href="<?php echo $base; ?>gardener/map.php" class="<?php echo get_dock_link_class('map.php', $bottom_page); ?>">
            <i class="bi bi-map-fill text-lg"></i>
            <span class="hidden sm:inline">Map</span>
        </a>
        <a href="<?php echo $base; ?>gardener/search.php"
            class="<?php echo get_dock_link_class('search.php', $bottom_page); ?>">
            <i class="bi bi-search text-lg"></i>
            <span class="hidden sm:inline">Search</span>
        </a>
        <a href="<?php echo $base; ?>gardener/requests.php"
            class="<?php echo get_dock_link_class('requests.php', $bottom_page); ?>">
            <i class="bi bi-send-fill text-lg"></i>
            <span class="hidden sm:inline">Requests</span>
        </a>
        <a href="<?php echo $base; ?>gardener/schedules.php"
            class="<?php echo get_dock_link_class('schedules.php', $bottom_page); ?>">
            <i class="bi bi-calendar-check-fill text-lg"></i>
            <span class="hidden sm:inline">Schedules</span>
        </a>
        <a href="<?php echo $base; ?>gardener/harvests.php"
            class="<?php echo get_dock_link_class('harvests.php', $bottom_page); ?>">
            <i class="bi bi-basket3-fill text-lg"></i>
            <span class="hidden sm:inline">Harvests</span>
        </a>
    <?php endif; ?>
</div>



<!-- Bootstrap Bundle with Popper JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Leaflet.js Map script (Loads standard OpenStreetMap map) -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<!-- Initialize Tooltips and Mobile Navigation Toggle -->
<script>
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))

    // Mobile Sidebar Toggle JS
    document.addEventListener("DOMContentLoaded", function () {
        const toggleBtn = document.getElementById('sidebarToggleBtn');
        const sidebar = document.querySelector('.sidebar');
        const backdrop = document.getElementById('sidebarBackdrop');

        if (toggleBtn && sidebar && backdrop) {
            toggleBtn.addEventListener('click', function () {
                sidebar.classList.toggle('show');
                backdrop.classList.toggle('show');
            });

            backdrop.addEventListener('click', function () {
                sidebar.classList.remove('show');
                backdrop.classList.remove('show');
            });
        }
    });
</script>

</body>

</html>