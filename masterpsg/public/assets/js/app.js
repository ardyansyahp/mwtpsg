// Basic Navigation & UI Logic
document.addEventListener('DOMContentLoaded', () => {
    // 1. Sidebar Toggle Mobile
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', (e) => {
            e.preventDefault();
            sidebar.classList.toggle('hidden');
            sidebarOverlay.classList.toggle('hidden');
        });
    }

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', () => {
            sidebar.classList.add('hidden');
            sidebarOverlay.classList.add('hidden');
        });
    }

    // 2. Submenu Toggling
    const toggles = [
        { id: 'dashboardToggle', submenu: 'dashboardSubmenu', arrow: 'dashboardArrow' },
        { id: 'masterDataToggle', submenu: 'masterDataSubmenu', arrow: 'masterDataArrow' },
        { id: 'subMasterToggle', submenu: 'subMasterSubmenu', arrow: 'subMasterArrow' },
        { id: 'bahanBakuToggle', submenu: 'bahanBakuSubmenu', arrow: 'bahanBakuArrow' },
        { id: 'produksiToggle', submenu: 'produksiSubmenu', arrow: 'produksiArrow' },
        { id: 'finishGoodToggle', submenu: 'finishGoodSubmenu', arrow: 'finishGoodArrow' },
        { id: 'shippingToggle', submenu: 'shippingSubmenu', arrow: 'shippingArrow' }
    ];

    toggles.forEach(item => {
        const toggleEl = document.getElementById(item.id);
        const submenuEl = document.getElementById(item.submenu);
        const arrowEl = document.getElementById(item.arrow);

        if (toggleEl && submenuEl) {
            // Initialize: If parent is marked as active, show submenu
            if (toggleEl.classList.contains('active')) {
                submenuEl.classList.remove('hidden');
                if (arrowEl) arrowEl.classList.add('rotate-180');
            }

            toggleEl.addEventListener('click', (e) => {
                e.preventDefault();
                const isHidden = submenuEl.classList.contains('hidden');

                // Close other submenus (Optional but recommended for cleaner UI)
                toggles.forEach(other => {
                    if (other.id !== item.id) {
                        const otherSub = document.getElementById(other.submenu);
                        const otherArrow = document.getElementById(other.arrow);
                        if (otherSub) otherSub.classList.add('hidden');
                        if (otherArrow) otherArrow.classList.remove('rotate-180');
                    }
                });

                submenuEl.classList.toggle('hidden');
                if (arrowEl) {
                    arrowEl.classList.toggle('rotate-180');
                }
            });
        }
    });

    // 3. Clock Update (Asia/Jakarta)
    function updateDateTime() {
        const dateTimeElement = document.getElementById('currentDateTime');
        if (dateTimeElement) {
            const now = new Date();
            const options = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                timeZone: 'Asia/Jakarta'
            };
            const dateStr = now.toLocaleDateString('id-ID', options);
            dateTimeElement.textContent = dateStr;
        }
    }

    if (document.getElementById('currentDateTime')) {
        updateDateTime();
        setInterval(updateDateTime, 1000);
    }

    // 4. Compatibility Layer (to prevent errors in old scripts)
    window.tabManager = {
        addTab: function (id, title, url) {
            if (url && typeof url === 'string' && (url.startsWith('/') || url.startsWith('http'))) {
                window.location.href = url;
            } else {
                // Try to find route from old ID if url is empty
                const routeMap = {
                    'dashboard': '/home',
                    'master-perusahaan': '/master/perusahaan',
                    'master-mold': '/submaster/mold',
                    'master-bahanbaku': '/submaster/bahanbaku',
                    'master-mesin': '/master/mesin',
                    'master-manpower': '/master/manpower',
                    'master-plantgate': '/master/plantgate',
                    'master-kendaraan': '/master/kendaraan',
                    'planning-editor': '/planning',
                    'planning-matriks': '/planning/matriks',
                    'bahanbaku-dashboard': '/bahanbaku/dashboard',
                    'bahanbaku-receiving': '/bahanbaku/receiving',
                    'bahanbaku-supply': '/bahanbaku/supply',
                    'submaster-part': '/submaster/part',
                    'submaster-plantgatepart': '/submaster/plantgatepart',
                    'produksi-inject-dashboard': '/produksi/inject/dashboard',
                    'produksi-inject': '/produksi/inject',
                    'produksi-inject-out': '/produksi/inject/out',
                    'produksi-wip-dashboard': '/produksi/wip/dashboard',
                    'produksi-wip-in': '/produksi/wip/in',
                    'produksi-wip-out': '/produksi/wip/out',
                    'produksi-assy-dashboard': '/produksi/assy/dashboard',
                    'produksi-assy-in': '/produksi/assy/in',
                    'produksi-assy-out': '/produksi/assy/out',
                    'finishgood-in-dashboard': '/finishgood/in/dashboard',
                    'finishgood-in': '/finishgood/in',
                    'finishgood-out-dashboard': '/finishgood/out/dashboard',
                    'finishgood-out': '/finishgood/out',
                    'shipping-controltruck': '/shipping/controltruck/monitoring',
                    'shipping-delivery-dashboard': '/shipping/delivery/dashboard',
                    'shipping-delivery': '/shipping/delivery',
                    'tracer': '/tracer',
                    'spk': '/spk',
                    'control-supplier': '/controlsupplier/monitoring',
                    'control-supplier-dashboard': '/controlsupplier/dashboard',
                };
                if (routeMap[id]) {
                    window.location.href = routeMap[id];
                } else if (id && id.startsWith('/')) {
                    window.location.href = id;
                }
            }
        },
        reloadCurrentTab: function () {
            window.location.reload();
        },
        closeCurrentTab: function () {
            window.history.back();
        },
        refreshTab: function () {
            window.location.reload();
        }
    };
});
