document.addEventListener("DOMContentLoaded", function () {
    let schedulesList = [];

    // DOM Elements
    const schedulesContainer = document.getElementById('schedulesContainer');
    const addTaskModalEl = document.getElementById('addTaskModal');
    const addTaskForm = document.getElementById('addTaskForm');

    // Load schedules
    async function loadSchedules() {
        try {
            const response = await fetch('../api/schedules.php');
            const res = await response.json();
            if (res.status === 'success') {
                schedulesList = res.data.schedules;
                renderSchedules();
            } else {
                console.error("API error:", res.message);
            }
        } catch (err) {
            console.error("Failed to load schedules:", err);
        }
    }

    // Format Date helper
    function formatDateTime(dateTimeStr) {
        const d = new Date(dateTimeStr);
        if (isNaN(d.getTime())) return dateTimeStr;
        return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) + ' ' +
            d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
    }

    // Format Time helper
    function formatTime(dateTimeStr) {
        const d = new Date(dateTimeStr);
        if (isNaN(d.getTime())) return dateTimeStr;
        return d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
    }

    // Render schedules
    function renderSchedules() {
        if (schedulesList.length === 0) {
            schedulesContainer.innerHTML = `
                <div class="text-center py-5 text-muted border rounded-4 bg-white">
                    <i class="bi bi-calendar-event fs-1 d-block mb-2"></i>
                    <span>No tasks scheduled yet. Click "+ Add Task" to set a deadline.</span>
                </div>
            `;
            return;
        }

        schedulesContainer.innerHTML = schedulesList.map(sched => {
            let iconHtml = '';
            if (sched.task_type === 'planting') {
                iconHtml = `<i class="bi bi-seedling text-success fs-4"></i>`;
            } else if (sched.task_type === 'watering') {
                iconHtml = `<i class="bi bi-droplet-fill text-primary fs-4"></i>`;
            } else if (sched.task_type === 'weeding') {
                iconHtml = `<i class="bi bi-scissors text-warning fs-4"></i>`;
            } else {
                iconHtml = `<i class="bi bi-calendar-check-fill text-secondary fs-4"></i>`;
            }

            const formattedStart = formatDateTime(sched.start_time);
            const formattedEnd = formatTime(sched.end_time);

            return `
                <div class="card border rounded-4  mb-3" style="border-color: var(--drive-border) !important;">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="d-flex align-items-center gap-2">
                                ${iconHtml}
                                <h3 class="fs-6 fw-bold m-0 text-dark">${sched.title}</h3>
                            </div>
                            <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1" style="font-size: 10px;">
                                ${sched.status.charAt(0).toUpperCase() + sched.status.slice(1)}
                            </span>
                        </div>

                        <p class="text-secondary mb-3" style="font-size: 0.825rem;">${sched.description}</p>
                        
                        <div class="row g-2 p-3 bg-light rounded-3 text-secondary mb-1" style="font-size: 0.75rem;">
                            <div class="col-sm-6"><i class="bi bi-geo-alt-fill me-1"></i> <strong>Location:</strong> ${sched.land_title}</div>
                            <div class="col-sm-6"><i class="bi bi-person-fill me-1"></i> <strong>Assigned to:</strong> ${sched.gardener ? sched.gardener : 'Unassigned'}</div>
                            <div class="col-sm-12"><i class="bi bi-clock-fill me-1"></i> <strong>Time:</strong> ${formattedStart} - ${formattedEnd}</div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    // Submit Add Task form
    if (addTaskForm) {
        addTaskForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            const payload = {
                action: 'create_schedule',
                title: document.getElementById('title').value,
                task_type: document.getElementById('task_type').value,
                start_time: document.getElementById('start_time').value,
                end_time: document.getElementById('end_time').value,
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

    // Initial load
    loadSchedules();
});
