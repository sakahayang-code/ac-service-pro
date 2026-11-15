// AC SERVICE PRO - Core Application JS v1.0

document.addEventListener('DOMContentLoaded', () => {
    // 1. Register Service Worker
    if ('serviceWorker' in navigator) {
        const baseUrl = window.APP_BASE_URL || '';
        navigator.serviceWorker.register(baseUrl + '/service-worker.js', { scope: (baseUrl || '') + '/' })
            .then((reg) => console.log('ServiceWorker registered with scope:', reg.scope))
            .catch((err) => console.warn('ServiceWorker registration error:', err));
    }

    // 2. Setup PWA Install Prompt
    let deferredInstallPrompt = null;
    const installBanner = document.getElementById('pwa-install-banner');
    const installBtn = document.getElementById('pwa-install-btn');

    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredInstallPrompt = e;
        if (installBanner) {
            installBanner.style.display = 'flex';
        }
    });

    if (installBtn) {
        installBtn.addEventListener('click', async () => {
            if (deferredInstallPrompt) {
                deferredInstallPrompt.prompt();
                const choice = await deferredInstallPrompt.userChoice;
                if (choice.outcome === 'accepted') {
                    console.log('User installed PWA');
                }
                deferredInstallPrompt = null;
                if (installBanner) installBanner.style.display = 'none';
            }
        });
    }

    // 3. Network Status Indicator
    const updateNetworkStatus = () => {
        const badge = document.getElementById('network-status-badge');
        if (badge) {
            if (navigator.onLine) {
                badge.innerHTML = '<span class="w-2 h-2 rounded-full bg-emerald-500 inline-block mr-1"></span> Online';
                badge.className = 'badge badge-green';
            } else {
                badge.innerHTML = '<span class="w-2 h-2 rounded-full bg-amber-500 inline-block mr-1"></span> Offline';
                badge.className = 'badge badge-amber';
            }
        }
    };
    window.addEventListener('online', updateNetworkStatus);
    window.addEventListener('offline', updateNetworkStatus);
    updateNetworkStatus();

    // 4. Global Role Switcher Listener
    const roleSelect = document.getElementById('quick-role-switcher');
    if (roleSelect) {
        roleSelect.addEventListener('change', (e) => {
            const role = e.target.value;
            if (role) {
                window.location.href = `/auth/switch-role?role=${encodeURIComponent(role)}`;
            }
        });
    }
});

// Toast notification function
window.showToast = function(message, type = 'info') {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'toast-container';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `
        <div class="flex-1">${message}</div>
        <button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;cursor:pointer;color:#94a3b8;font-size:16px;">&times;</button>
    `;

    container.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(50px)';
        toast.style.transition = 'all 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
};

// Modal helpers
window.openModal = function(id) {
    const el = document.getElementById(id);
    if (el) {
        el.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
};

window.closeModal = function(id) {
    const el = document.getElementById(id);
    if (el) {
        el.classList.remove('active');
        document.body.style.overflow = '';
    }
};

// Signature Pad Implementation
window.initSignaturePad = function(canvasId) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return null;

    const ctx = canvas.getContext('2d');
    let isDrawing = false;
    let hasDrawn = false;

    // Adjust for high-DPI displays
    const ratio = Math.max(window.devicePixelRatio || 1, 1);
    const rect = canvas.getBoundingClientRect();
    canvas.width = rect.width * ratio;
    canvas.height = rect.height * ratio;
    ctx.scale(ratio, ratio);

    ctx.strokeStyle = '#0f2a4a';
    ctx.lineWidth = 2.5;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';

    function getPos(e) {
        const r = canvas.getBoundingClientRect();
        if (e.touches && e.touches.length > 0) {
            return {
                x: e.touches[0].clientX - r.left,
                y: e.touches[0].clientY - r.top
            };
        }
        return {
            x: e.clientX - r.left,
            y: e.clientY - r.top
        };
    }

    function start(e) {
        isDrawing = true;
        hasDrawn = true;
        const pos = getPos(e);
        ctx.beginPath();
        ctx.moveTo(pos.x, pos.y);
        e.preventDefault();
    }

    function move(e) {
        if (!isDrawing) return;
        const pos = getPos(e);
        ctx.lineTo(pos.x, pos.y);
        ctx.stroke();
        e.preventDefault();
    }

    function end() {
        isDrawing = false;
    }

    canvas.addEventListener('mousedown', start);
    canvas.addEventListener('mousemove', move);
    window.addEventListener('mouseup', end);

    canvas.addEventListener('touchstart', start, { passive: false });
    canvas.addEventListener('touchmove', move, { passive: false });
    window.addEventListener('touchend', end);

    return {
        clear: () => {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            hasDrawn = false;
        },
        isEmpty: () => !hasDrawn,
        toDataURL: () => canvas.toDataURL('image/png')
    };
};
