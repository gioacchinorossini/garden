/**
 * IdleLand Tutorial Navigation System
 * First-time launch walkthrough for Landowners and Gardeners
 * Cats & Soup Game Aesthetic
 */
(function () {
    'use strict';

    // ── Role Detection ──
    function getUserRole() {
        if (typeof window.CURRENT_USER_ROLE !== 'undefined' && window.CURRENT_USER_ROLE) {
            return window.CURRENT_USER_ROLE.toLowerCase();
        }
        const path = window.location.pathname.toLowerCase();
        if (path.includes('/landowner/')) return 'landowner';
        if (path.includes('/gardener/')) return 'gardener';
        return null;
    }

    // ── Base Path Calculation ──
    function getBasePath() {
        const path = window.location.pathname;
        if (path.includes('/landowner/') || path.includes('/gardener/') || path.includes('/admin/')) {
            return '../';
        }
        return '';
    }

    const role = getUserRole();
    if (!role || (role !== 'landowner' && role !== 'gardener')) {
        return; // Only active for landowners and gardeners
    }

    const BASE = getBasePath();
    const STORAGE_KEY = 'idleland_tutorial_' + role + '_completed';

    // ── Tutorial Step Definitions ──
    const TUTORIAL_DATA = {
        landowner: [
            {
                centered: true,
                badge: '🌱 Landowner Onboarding',
                iconHtml: `<img src="${BASE}logo.jpeg" alt="IdleLand" style="width:36px;height:36px;border-radius:50%;object-fit:cover;" />`,
                title: 'Welcome, Landowner!',
                subtitle: 'Your Urban Agriculture Journey Begins',
                desc: 'Turn your idle vacant properties into thriving, eco-friendly community gardens! Connect with local urban growers, review planting proposals, monitor environmental impact, and help cultivate sustainable neighborhood food.',
                actionLink: null,
                targetDesktop: null,
                targetMobile: null
            },
            {
                centered: false,
                badge: '🗺️ Interactive Map',
                iconHtml: `<i class="bi bi-geo-alt-fill text-emerald-600" style="font-size:1.6rem;"></i>`,
                title: 'Gardens & Parcels Map',
                subtitle: 'Your City-Wide Command Center',
                desc: 'Explore available vacant lands and active community plots across the city in both 2D map view and 3D terrain buildings. Inspect sunlight, hydration, and neighborhood green density.',
                actionLink: { text: 'Go to Gardens Map', url: `${BASE}landowner/dashboard.php` },
                targetDesktop: '#mainSidebar a[href*="dashboard.php"]',
                targetMobile: '#mobileBottomNavDock a[href*="dashboard.php"]'
            },
            {
                centered: false,
                badge: '📝 List Your Land',
                iconHtml: `<img src="${BASE}assets/images/tasks/planting.png" alt="Register Land" style="width:34px;height:34px;object-fit:contain;" />`,
                title: 'Register Land & Plots',
                subtitle: 'Publish Your Idle Property',
                desc: 'Easily list your parcel with photos and ownership proofs. Subdivide your property into individual gardening plots, set permitted crops, and specify lease durations or free community stewardship.',
                actionLink: { text: 'Open Registration', url: `${BASE}landowner/register.php` },
                targetDesktop: '#mainSidebar a[href*="register.php"], #registerLandFab',
                targetMobile: '#registerLandFab, #mobileBottomNavDock a[href*="register.php"]'
            },
            {
                centered: false,
                badge: '🏡 Manage Properties',
                iconHtml: `<i class="bi bi-tree-fill text-emerald-700" style="font-size:1.6rem;"></i>`,
                title: 'My Lands & Plot Controls',
                subtitle: 'Manage Parcels, Plots & Crops',
                desc: 'View all your registered properties. Add custom plots, set permitted crops, inspect assigned gardeners, and track real-time occupancy. You can also view interactive satellite maps of each parcel.',
                actionLink: { text: 'View My Lands', url: `${BASE}landowner/lands.php` },
                targetDesktop: '#mainSidebar a[href*="lands.php"]',
                targetMobile: '#mobileBottomNavDock a[href*="lands.php"]'
            },
            {
                centered: false,
                badge: '📋 Tenancy Requests',
                iconHtml: `<i class="bi bi-file-earmark-check-fill text-blue-600" style="font-size:1.6rem;"></i>`,
                title: 'Gardener Applications',
                subtitle: 'Review & Approve Plot Stewards',
                desc: 'Passionate local gardeners will submit applications detailing the organic crops they plan to cultivate. Review their experience, view their proposed planting schedule, and approve with a single tap.',
                actionLink: { text: 'Check Requests', url: `${BASE}landowner/requests.php` },
                targetDesktop: '#mainSidebar a[href*="requests.php"]',
                targetMobile: '#mobileBottomNavDock a[href*="requests.php"]'
            },
            {
                centered: false,
                badge: '📅 Garden Tasks & Eco-Impact',
                iconHtml: `<img src="${BASE}assets/images/tasks/watering.png" alt="Schedules" style="width:34px;height:34px;object-fit:contain;" />`,
                title: 'Tasks & Stewardship Score',
                subtitle: 'Coordinate Care & Track Real Impact',
                desc: 'Coordinate maintenance, watering, compost routines, and harvest milestones. Watch your live environmental stewardship score climb: CO₂ absorbed, liters of water managed, and consecutive care streak rewards!',
                actionLink: { text: 'Open Schedules', url: `${BASE}landowner/schedules.php` },
                targetDesktop: '#mainSidebar a[href*="schedules.php"]',
                targetMobile: '#mobileBottomNavDock a[href*="schedules.php"], #addTaskFab'
            },
            {
                centered: true,
                badge: '🎉 Ready to Cultivate!',
                iconHtml: `<i class="bi bi-patch-check-fill text-emerald-600" style="font-size:2rem;"></i>`,
                title: "You're All Set, Landowner!",
                subtitle: 'Let’s Turn Idle Lands into Green Spaces',
                desc: 'You now know all the essentials of navigating IdleLand. Start by exploring the map or registering your first parcel. You can replay this guide anytime from the top navigation bar or your profile menu.',
                actionLink: null,
                targetDesktop: null,
                targetMobile: null
            }
        ],

        gardener: [
            {
                centered: true,
                badge: '🌾 Gardener Onboarding',
                iconHtml: `<img src="${BASE}logo.jpeg" alt="IdleLand" style="width:36px;height:36px;border-radius:50%;object-fit:cover;" />`,
                title: 'Welcome, Gardener!',
                subtitle: 'Grow Fresh Food in Your Neighborhood',
                desc: 'IdleLand connects you with verified idle lots in your city. Claim open plots, plant organic vegetables and herbs, gain farming experience, earn care points, and harvest healthy produce for your community.',
                actionLink: null,
                targetDesktop: null,
                targetMobile: null
            },
            {
                centered: false,
                badge: '🗺️ Explore Lands',
                iconHtml: `<i class="bi bi-geo-alt-fill text-emerald-600" style="font-size:1.6rem;"></i>`,
                title: 'Explore Urban Gardens',
                subtitle: 'Interactive 2D & 3D Parcel Map',
                desc: 'Find available community plots near your home. View live weather, soil hydration, sun exposure, and click any garden pin to view available plot spaces and landowner terms.',
                actionLink: { text: 'Open Gardens Map', url: `${BASE}gardener/dashboard.php` },
                targetDesktop: '#mainSidebar a[href*="dashboard.php"]',
                targetMobile: '#mobileBottomNavDock a[href*="dashboard.php"]'
            },
            {
                centered: false,
                badge: '🌱 Browse & Apply',
                iconHtml: `<i class="bi bi-compass-fill text-teal-600" style="font-size:1.6rem;"></i>`,
                title: 'Browse Lands & Applications',
                subtitle: 'Find Your Plot & Track Approvals',
                desc: 'Browse verified parcels, filter by crop types and amenities, and apply for your preferred plot with your gardening proposal. You can monitor your tenancy status right under My Requests.',
                actionLink: { text: 'Browse Lands', url: `${BASE}gardener/browse.php` },
                targetDesktop: '#mainSidebar a[href*="browse.php"], #mainSidebar a[href*="requests.php"]',
                targetMobile: '#mobileBottomNavDock a[href*="requests.php"]'
            },
            {
                centered: false,
                badge: '⭐ Daily Tasks & Quests',
                iconHtml: `<img src="${BASE}assets/images/tasks/weeding.png" alt="Schedules" style="width:34px;height:34px;object-fit:contain;" />`,
                title: 'Schedules & Stewardship Chores',
                subtitle: 'Earn Points, Level Up & Care for Crops',
                desc: 'Check your daily garden tasks: watering, weeding, mulching, compost feeding, and trellising. Mark chores complete to earn stewardship XP, heart affinity with plants, and unlock new grower tiers!',
                actionLink: { text: 'Open Schedules', url: `${BASE}gardener/schedules.php` },
                targetDesktop: '#mainSidebar a[href*="schedules.php"]',
                targetMobile: '#mobileBottomNavDock a[href*="schedules.php"]'
            },
            {
                centered: false,
                badge: '🥕 Record Harvests',
                iconHtml: `<img src="${BASE}assets/images/tasks/harvesting.png" alt="Harvest" style="width:34px;height:34px;object-fit:contain;" />`,
                title: 'Log Fresh Crop Yields',
                subtitle: 'Track Your Community Nutrition Impact',
                desc: 'When crops are ripe, log your harvest details: produce type, kilograms harvested, and photos. Track your lifetime yield, celebrate successful growing seasons, and share food with neighbors!',
                actionLink: { text: 'Record Harvest', url: `${BASE}gardener/harvests.php` },
                targetDesktop: '#mainSidebar a[href*="harvests.php"]',
                targetMobile: '#mobileBottomNavDock a[href*="harvests.php"]'
            },
            {
                centered: true,
                badge: '🎉 Ready to Grow!',
                iconHtml: `<i class="bi bi-check-circle-fill text-emerald-600" style="font-size:2rem;"></i>`,
                title: "You're All Set, Gardener!",
                subtitle: 'Happy Urban Planting & Growing',
                desc: 'Your green thumb journey starts now! Explore the gardens map, apply for a plot, or check your chores. You can replay this guide anytime from the top navigation bar or your profile menu.',
                actionLink: null,
                targetDesktop: null,
                targetMobile: null
            }
        ]
    };

    let currentStepIndex = 0;
    let overlayEl = null;
    let cardEl = null;
    let activeSpotlightEl = null;
    let activeParentNavEl = null;
    const steps = TUTORIAL_DATA[role] || [];

    // ── Helper: Safe DOM Creation ──
    function createTutorialDOM() {
        if (overlayEl) return;

        // SVG Mask Overlay with EvenOdd Path Cutout
        const svgNS = 'http://www.w3.org/2000/svg';
        overlayEl = document.createElementNS(svgNS, 'svg');
        overlayEl.id = 'idlelandTutorialSvg';
        overlayEl.setAttribute('class', 'idleland-tutorial-svg-overlay');
        overlayEl.setAttribute('width', '100%');
        overlayEl.setAttribute('height', '100%');

        const path = document.createElementNS(svgNS, 'path');
        path.id = 'idlelandTutorialPath';
        path.setAttribute('class', 'idleland-tutorial-path');
        path.setAttribute('fill-rule', 'evenodd');

        const ring = document.createElementNS(svgNS, 'rect');
        ring.id = 'idlelandTutorialRing';
        ring.setAttribute('class', 'tutorial-cutout-ring');
        ring.style.display = 'none';

        overlayEl.appendChild(path);
        overlayEl.appendChild(ring);

        cardEl = document.createElement('div');
        cardEl.id = 'idlelandTutorialCard';
        cardEl.className = 'idleland-tutorial-card';
        cardEl.setAttribute('role', 'dialog');
        cardEl.setAttribute('aria-modal', 'true');

        document.body.appendChild(overlayEl);
        document.body.appendChild(cardEl);

        // Click outside closes if desired, or skip
        overlayEl.addEventListener('click', function (e) {
            // Gentle pulse to signal user should use navigation buttons
            cardEl.style.transform = (window.innerWidth < 768 && cardEl.classList.contains('tutorial-centered'))
                ? 'translateY(-50%) scale(1.03)'
                : (cardEl.classList.contains('tutorial-centered') ? 'translate(-50%, -50%) scale(1.03)' : 'scale(1.03)');
            setTimeout(() => {
                if (!cardEl) return;
                cardEl.style.transform = (window.innerWidth < 768 && cardEl.classList.contains('tutorial-centered'))
                    ? 'translateY(-50%)'
                    : (cardEl.classList.contains('tutorial-centered') ? 'translate(-50%, -50%)' : 'none');
            }, 150);
        });

        // Keyboard navigation
        document.addEventListener('keydown', handleKeyNav);
        window.addEventListener('resize', handleWindowResize);
        window.addEventListener('scroll', handleWindowScroll, { passive: true });
    }

    function handleKeyNav(e) {
        if (!overlayEl || !overlayEl.classList.contains('active')) return;
        if (e.key === 'ArrowRight' || e.key === 'Enter') {
            e.preventDefault();
            nextStep();
        } else if (e.key === 'ArrowLeft') {
            e.preventDefault();
            prevStep();
        } else if (e.key === 'Escape') {
            e.preventDefault();
            finishTutorial(false);
        }
    }

    let resizeTimer = null;
    function handleWindowResize() {
        if (!overlayEl || !overlayEl.classList.contains('active')) return;
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            renderCurrentStep(false);
        }, 100);
    }

    function handleWindowScroll() {
        if (!overlayEl || !overlayEl.classList.contains('active')) return;
        if (activeSpotlightEl) {
            updateSvgCutout(activeSpotlightEl);
            positionCard(activeSpotlightEl, false);
        }
    }

    // ── SVG Cutout Hole & Ring Calculation ──
    function updateSvgCutout(targetEl) {
        if (!overlayEl) return;
        const path = overlayEl.querySelector('#idlelandTutorialPath');
        const ring = overlayEl.querySelector('#idlelandTutorialRing');
        if (!path || !ring) return;

        const W = window.innerWidth;
        const H = window.innerHeight;
        overlayEl.setAttribute('viewBox', `0 0 ${W} ${H}`);

        if (!targetEl) {
            path.setAttribute('d', `M 0 0 L ${W} 0 L ${W} ${H} L 0 ${H} Z`);
            ring.style.display = 'none';
            return;
        }

        const rect = targetEl.getBoundingClientRect();
        const pad = 6;
        const x = Math.max(0, rect.left - pad);
        const y = Math.max(0, rect.top - pad);
        const w = rect.width + (pad * 2);
        const h = rect.height + (pad * 2);
        const r = Math.min(18, Math.min(w, h) / 2);

        // Outer full-screen rect + inner rounded rect hole with evenodd fill rule
        const d = `M 0,0 L ${W},0 L ${W},${H} L 0,${H} Z ` +
                  `M ${x + r},${y} ` +
                  `L ${x + w - r},${y} ` +
                  `A ${r},${r} 0 0,1 ${x + w},${y + r} ` +
                  `L ${x + w},${y + h - r} ` +
                  `A ${r},${r} 0 0,1 ${x + w - r},${y + h} ` +
                  `L ${x + r},${y + h} ` +
                  `A ${r},${r} 0 0,1 ${x},${y + h - r} ` +
                  `L ${x},${y + r} ` +
                  `A ${r},${r} 0 0,1 ${x + r},${y} Z`;

        path.setAttribute('d', d);

        ring.setAttribute('x', x);
        ring.setAttribute('y', y);
        ring.setAttribute('width', w);
        ring.setAttribute('height', h);
        ring.setAttribute('rx', r);
        ring.setAttribute('ry', r);
        ring.style.display = 'block';
    }

    // ── Highlight Element Controller ──
    function clearSpotlight() {
        if (activeSpotlightEl) {
            activeSpotlightEl.classList.remove('idleland-tutorial-elevated');
            activeSpotlightEl.classList.remove('idleland-tutorial-spotlight');
            if (activeSpotlightEl.dataset.tutorialOldPosition) {
                activeSpotlightEl.style.position = '';
                delete activeSpotlightEl.dataset.tutorialOldPosition;
            }
            activeSpotlightEl = null;
        }
        if (activeParentNavEl) {
            activeParentNavEl.classList.remove('idleland-nav-elevated');
            activeParentNavEl = null;
        }
        if (overlayEl) {
            const path = overlayEl.querySelector('#idlelandTutorialPath');
            const ring = overlayEl.querySelector('#idlelandTutorialRing');
            if (path) {
                const W = window.innerWidth;
                const H = window.innerHeight;
                overlayEl.setAttribute('viewBox', `0 0 ${W} ${H}`);
                path.setAttribute('d', `M 0 0 L ${W} 0 L ${W} ${H} L 0 ${H} Z`);
            }
            if (ring) {
                ring.style.display = 'none';
            }
        }
    }

    function isElementVisible(el) {
        if (!el) return false;
        const style = window.getComputedStyle(el);
        if (style.display === 'none' || style.visibility === 'hidden' || style.opacity === '0') {
            return false;
        }
        const rect = el.getBoundingClientRect();
        return rect.width > 0 && rect.height > 0;
    }

    function findTargetElement(step) {
        const isMobile = window.innerWidth < 768;
        const selector = isMobile ? (step.targetMobile || step.targetDesktop) : (step.targetDesktop || step.targetMobile);
        if (!selector) return null;

        // Try comma separated selectors
        const parts = selector.split(',').map(s => s.trim());
        for (const sel of parts) {
            const el = document.querySelector(sel);
            if (el && isElementVisible(el)) {
                return el;
            }
        }
        return null;
    }

    // ── Position Card relative to Spotlight ──
    function positionCard(targetEl, isCentered) {
        if (!cardEl) return;

        const isMobile = window.innerWidth < 768;

        if (isMobile) {
            if (isCentered || !targetEl) {
                cardEl.className = 'idleland-tutorial-card tutorial-centered';
                cardEl.style.top = '50%';
                cardEl.style.left = '10px';
                cardEl.style.right = '10px';
                cardEl.style.bottom = 'auto';
                cardEl.style.transform = 'translateY(-50%)';
                cardEl.removeAttribute('data-placement');
            } else {
                cardEl.className = 'idleland-tutorial-card';
                cardEl.style.left = '10px';
                cardEl.style.right = '10px';
                cardEl.style.transform = 'none';

                const rect = targetEl.getBoundingClientRect();
                // If element is in lower 55% of the viewport (e.g. mobile dock or FAB), place above target
                if (rect.top > window.innerHeight * 0.45) {
                    const bottomSpace = Math.max(16, window.innerHeight - rect.top + 16);
                    cardEl.style.bottom = `${bottomSpace}px`;
                    cardEl.style.top = 'auto';
                    cardEl.setAttribute('data-placement', 'top');
                } else {
                    // Place below target
                    const topSpace = Math.max(16, rect.bottom + 16);
                    cardEl.style.top = `${topSpace}px`;
                    cardEl.style.bottom = 'auto';
                    cardEl.setAttribute('data-placement', 'bottom');
                }
            }
            return;
        }

        // Desktop positioning
        if (isCentered || !targetEl) {
            cardEl.className = 'idleland-tutorial-card tutorial-centered';
            cardEl.style.top = '50%';
            cardEl.style.left = '50%';
            cardEl.style.right = 'auto';
            cardEl.style.bottom = 'auto';
            cardEl.style.transform = 'translate(-50%, -50%)';
            cardEl.removeAttribute('data-placement');
            return;
        }

        const rect = targetEl.getBoundingClientRect();
        cardEl.className = 'idleland-tutorial-card';
        cardEl.style.transform = 'none';

        // Check if target is inside mainSidebar
        const isInSidebar = targetEl.closest('#mainSidebar');

        if (isInSidebar) {
            // Position to the right of sidebar item
            const cardLeft = rect.right + 24;
            const cardTop = Math.max(20, Math.min(window.innerHeight - 380, rect.top - 30));

            cardEl.style.left = `${cardLeft}px`;
            cardEl.style.top = `${cardTop}px`;
            cardEl.style.right = 'auto';
            cardEl.style.bottom = 'auto';
            cardEl.setAttribute('data-placement', 'right');
        } else {
            // General element positioning (e.g. FAB or top header)
            if (rect.top > window.innerHeight * 0.5) {
                // Place above
                const bottomSpace = Math.max(20, window.innerHeight - rect.top + 16);
                cardEl.style.left = `${Math.max(20, Math.min(window.innerWidth - 460, rect.left - 160))}px`;
                cardEl.style.bottom = `${bottomSpace}px`;
                cardEl.style.top = 'auto';
                cardEl.setAttribute('data-placement', 'bottom');
            } else {
                // Place below
                cardEl.style.left = `${Math.max(20, Math.min(window.innerWidth - 460, rect.left - 160))}px`;
                cardEl.style.top = `${rect.bottom + 16}px`;
                cardEl.style.bottom = 'auto';
                cardEl.setAttribute('data-placement', 'top');
            }
            cardEl.style.right = 'auto';
        }
    }

    // ── Render Step Content ──
    function renderCurrentStep(animate = true) {
        createTutorialDOM();
        clearSpotlight();

        if (currentStepIndex < 0) currentStepIndex = 0;
        if (currentStepIndex >= steps.length) {
            finishTutorial(true);
            return;
        }

        const step = steps[currentStepIndex];
        const isFirst = currentStepIndex === 0;
        const isLast = currentStepIndex === steps.length - 1;

        // Find target element
        let targetEl = null;
        if (!step.centered) {
            targetEl = findTargetElement(step);
            if (targetEl) {
                activeSpotlightEl = targetEl;
                if (window.getComputedStyle(targetEl).position === 'static') {
                    targetEl.dataset.tutorialOldPosition = 'static';
                    targetEl.style.position = 'relative';
                }
                targetEl.classList.add('idleland-tutorial-elevated');

                // Check and elevate parent container if inside navbar / dock / sidebar / fab
                const parentNav = targetEl.closest('#mobileBottomNavDock, #mainSidebar, .floating-land-fab, .floating-task-fab');
                if (parentNav) {
                    parentNav.classList.add('idleland-nav-elevated');
                    activeParentNavEl = parentNav;
                }

                // Ensure target is scrolled into view smoothly
                targetEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

                updateSvgCutout(targetEl);
            } else {
                updateSvgCutout(null);
            }
        } else {
            updateSvgCutout(null);
        }

        // Build Progress Dots
        let dotsHtml = '';
        for (let i = 0; i < steps.length; i++) {
            let dotClass = 'tutorial-dot';
            if (i === currentStepIndex) dotClass += ' active';
            else if (i < currentStepIndex) dotClass += ' passed';
            dotsHtml += `<div class="${dotClass}" title="Step ${i + 1}"></div>`;
        }

        // Action Link HTML
        let actionLinkHtml = '';
        if (step.actionLink) {
            actionLinkHtml = `
                <a href="${step.actionLink.url}" class="tutorial-jump-link">
                    <span>${step.actionLink.text}</span>
                    <i class="bi bi-arrow-right-short" style="font-size:1.1rem;line-height:1;"></i>
                </a>
            `;
        }

        // Build Card HTML
        cardEl.innerHTML = `
            <div class="tutorial-card-arrow"></div>

            <!-- Header -->
            <div class="tutorial-card-header">
                <span class="tutorial-step-counter">Step ${currentStepIndex + 1} of ${steps.length}</span>
                <button type="button" class="tutorial-close-btn" id="tutorialCloseBtn" title="Close Tour" aria-label="Close Tour">
                    <i class="bi bi-x"></i>
                </button>
            </div>

            <!-- Body -->
            <div class="tutorial-body-content">
                <div class="tutorial-icon-squircle">
                    ${step.iconHtml}
                </div>
                <div class="tutorial-text-block">
                    <div class="tutorial-subtitle">${step.subtitle}</div>
                    <h3 class="tutorial-title">${step.title}</h3>
                    <p class="tutorial-desc">${step.desc}</p>
                    ${actionLinkHtml}
                </div>
            </div>

            <!-- Progress Indicator -->
            <div class="tutorial-progress-bar">
                ${dotsHtml}
            </div>

            <!-- Footer -->
            <div class="tutorial-footer">
                <div>
                    ${!isLast ? `<button type="button" class="tutorial-skip-link" id="tutorialSkipBtn">Skip tour</button>` : `<span style="font-size:0.75rem;font-weight:700;color:#7FA668;">✓ Tutorial Complete</span>`}
                </div>
                <div class="tutorial-btn-group">
                    ${!isFirst ? `
                        <button type="button" class="tutorial-btn tutorial-btn--prev" id="tutorialPrevBtn">
                            <i class="bi bi-chevron-left text-xs"></i>
                            <span>Back</span>
                        </button>
                    ` : ''}

                    ${!isLast ? `
                        <button type="button" class="tutorial-btn tutorial-btn--next" id="tutorialNextBtn">
                            <span>Next</span>
                            <i class="bi bi-chevron-right text-xs"></i>
                        </button>
                    ` : `
                        <button type="button" class="tutorial-btn tutorial-btn--finish" id="tutorialFinishBtn">
                            <span>Get Started</span>
                            <i class="bi bi-arrow-right text-xs"></i>
                        </button>
                    `}
                </div>
            </div>
        `;

        // Position Card
        positionCard(targetEl, step.centered || !targetEl);

        // Bind Card Event Handlers
        const closeBtn = cardEl.querySelector('#tutorialCloseBtn');
        const skipBtn = cardEl.querySelector('#tutorialSkipBtn');
        const prevBtn = cardEl.querySelector('#tutorialPrevBtn');
        const nextBtn = cardEl.querySelector('#tutorialNextBtn');
        const finishBtn = cardEl.querySelector('#tutorialFinishBtn');

        if (closeBtn) closeBtn.onclick = () => finishTutorial(false);
        if (skipBtn) skipBtn.onclick = () => finishTutorial(false);
        if (prevBtn) prevBtn.onclick = prevStep;
        if (nextBtn) nextBtn.onclick = nextStep;
        if (finishBtn) finishBtn.onclick = () => finishTutorial(true);

        overlayEl.classList.add('active');

        if (animate) {
            cardEl.style.animation = 'none';
            // Trigger reflow
            void cardEl.offsetWidth;
            cardEl.style.animation = '';
        }
    }

    function nextStep() {
        if (currentStepIndex < steps.length - 1) {
            currentStepIndex++;
            renderCurrentStep(true);
        } else {
            finishTutorial(true);
        }
    }

    function prevStep() {
        if (currentStepIndex > 0) {
            currentStepIndex--;
            renderCurrentStep(true);
        }
    }

    function finishTutorial(markCompleted = true) {
        clearSpotlight();
        window.removeEventListener('scroll', handleWindowScroll);
        if (overlayEl) overlayEl.classList.remove('active');
        if (cardEl) {
            cardEl.style.opacity = '0';
            setTimeout(() => {
                if (cardEl && cardEl.parentNode) cardEl.parentNode.removeChild(cardEl);
                if (overlayEl && overlayEl.parentNode) overlayEl.parentNode.removeChild(overlayEl);
                overlayEl = null;
                cardEl = null;
            }, 300);
        }

        if (markCompleted) {
            try {
                localStorage.setItem(STORAGE_KEY, 'true');
            } catch (err) {
                console.warn('Unable to save tutorial status to localStorage:', err);
            }
        }
    }

    // ── Public Global API ──
    window.IdlelandTutorial = {
        start: function (force = false) {
            if (!force && localStorage.getItem(STORAGE_KEY) === 'true') {
                return; // Already completed
            }
            currentStepIndex = 0;
            renderCurrentStep(true);
        },
        next: nextStep,
        prev: prevStep,
        finish: () => finishTutorial(true),
        skip: () => finishTutorial(false),
        reset: function () {
            localStorage.removeItem(STORAGE_KEY);
            this.start(true);
        }
    };

    // ── Auto Launch on First Visit ──
    document.addEventListener('DOMContentLoaded', function () {
        const hasSeenTutorial = localStorage.getItem(STORAGE_KEY) === 'true';
        if (!hasSeenTutorial) {
            // Allow a small delay so maps, sidebars, and fonts initialize smoothly
            setTimeout(function () {
                window.IdlelandTutorial.start(false);
            }, 750);
        }
    });

})();
