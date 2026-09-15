/**
 * Interactive Sheet Slider
 * Enables smooth slide-down drag/swipe gestures for garden card sheets.
 */
(function (window) {
    'use strict';

    function initSheetSlider(options) {
        const sheet = typeof options.sheet === 'string' ? document.getElementById(options.sheet) : options.sheet;
        if (!sheet) return null;

        const backdrop = typeof options.backdrop === 'string' ? document.getElementById(options.backdrop) : options.backdrop;
        const handle = options.handle ? (typeof options.handle === 'string' ? sheet.querySelector(options.handle) : options.handle) : sheet.querySelector('.sheet-drag-handle-container, .sheet-drag-handle');
        const onClose = typeof options.onClose === 'function' ? options.onClose : function () {};

        let isDragging = false;
        let startY = 0;
        let startX = 0;
        let currentY = 0;
        let startTime = 0;
        let lastY = 0;
        let lastTime = 0;
        let hasMoved = false;
        let dragFromHandle = false;

        function resetStyles() {
            sheet.style.transform = '';
            sheet.style.opacity = '';
            sheet.style.transition = '';
            if (backdrop) {
                backdrop.style.opacity = '';
                backdrop.style.transition = '';
            }
        }

        function slideDownAndClose() {
            sheet.style.transition = 'transform 0.3s cubic-bezier(0.25, 0.8, 0.25, 1), opacity 0.25s ease';
            sheet.style.transform = 'translateY(120%)';
            sheet.style.opacity = '0';
            if (backdrop) {
                backdrop.style.transition = 'opacity 0.25s ease';
                backdrop.style.opacity = '0';
            }

            setTimeout(function () {
                onClose();
                resetStyles();
            }, 290);
        }

        function snapBack() {
            sheet.style.transition = 'transform 0.26s cubic-bezier(0.175, 0.885, 0.32, 1.15), opacity 0.2s ease';
            sheet.style.transform = 'translateY(0)';
            sheet.style.opacity = '1';
            if (backdrop) {
                backdrop.style.transition = 'opacity 0.2s ease';
                backdrop.style.opacity = '';
            }

            setTimeout(function () {
                if (!sheet.classList.contains('hidden')) {
                    resetStyles();
                }
            }, 280);
        }

        function onStart(e) {
            // Only left mouse click or touch
            if (e.type === 'mousedown' && e.button !== 0) return;
            if (sheet.classList.contains('hidden')) return;

            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;

            // Determine if clicking interactive elements inside the card (buttons, links, inputs)
            const target = e.target;
            const isHandle = handle && (handle === target || handle.contains(target));
            const isInteractive = target.closest('button, a, input, select, textarea, .btn, .btn-close');

            if (isInteractive && !isHandle) {
                return;
            }

            // If inside scrollable plot grid and not scrolled to top, don't hijack scroll
            const scrollable = target.closest('#sheetPlotsGrid, [style*="overflow-y: auto"]');
            if (scrollable && scrollable.scrollTop > 0 && !isHandle) {
                return;
            }

            isDragging = true;
            hasMoved = false;
            dragFromHandle = Boolean(isHandle);
            startY = clientY;
            startX = clientX;
            currentY = clientY;
            lastY = clientY;
            startTime = Date.now();
            lastTime = startTime;

            sheet.style.transition = 'none';
            if (backdrop) backdrop.style.transition = 'none';

            if (handle) {
                handle.classList.add('active-dragging');
            }
        }

        function onMove(e) {
            if (!isDragging) return;

            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const deltaY = clientY - startY;
            const deltaX = clientX - startX;

            // Check if horizontal motion dominates early on
            if (!hasMoved && Math.abs(deltaX) > Math.abs(deltaY) + 5 && !dragFromHandle) {
                isDragging = false;
                resetStyles();
                return;
            }

            // Detect intentional movement
            if (Math.abs(deltaY) > 3) {
                hasMoved = true;
                if (e.cancelable) {
                    e.preventDefault();
                }
            }

            if (!hasMoved) return;

            currentY = clientY;
            lastY = clientY;
            lastTime = Date.now();

            if (deltaY >= 0) {
                // Dragging down: move sheet down
                sheet.style.transform = 'translateY(' + deltaY + 'px)';
                if (backdrop) {
                    const fade = Math.max(0, 1 - (deltaY / 360));
                    backdrop.style.opacity = fade.toString();
                }
            } else {
                // Dragging up: rubber-band dampening
                const dampened = deltaY * 0.16;
                sheet.style.transform = 'translateY(' + dampened + 'px)';
            }
        }

        function onEnd() {
            if (!isDragging) return;
            isDragging = false;

            if (handle) {
                handle.classList.remove('active-dragging');
            }

            const deltaY = currentY - startY;
            const duration = Math.max(1, Date.now() - startTime);
            const velocity = deltaY / duration; // px per ms

            // If user simply clicked the handle without dragging
            if (!hasMoved && dragFromHandle) {
                slideDownAndClose();
                return;
            }

            // Dismiss conditions: dragged down > 70px or flicked downward fast (> 0.35 px/ms)
            if (deltaY > 70 || (deltaY > 25 && velocity > 0.35)) {
                slideDownAndClose();
            } else {
                snapBack();
            }
        }

        // Attach listeners
        // 1. Handle touch & mouse events
        sheet.addEventListener('touchstart', onStart, { passive: false });
        window.addEventListener('touchmove', onMove, { passive: false });
        window.addEventListener('touchend', onEnd);
        window.addEventListener('touchcancel', onEnd);

        sheet.addEventListener('mousedown', onStart);
        window.addEventListener('mousemove', onMove);
        window.addEventListener('mouseup', onEnd);

        // Click handler on handle for desktop/mobile tap
        if (handle) {
            handle.addEventListener('click', function (e) {
                if (!hasMoved) {
                    e.stopPropagation();
                    slideDownAndClose();
                }
            });
        }

        return {
            slideDownAndClose: slideDownAndClose,
            resetStyles: resetStyles
        };
    }

    window.initSheetSlider = initSheetSlider;
})(window);
