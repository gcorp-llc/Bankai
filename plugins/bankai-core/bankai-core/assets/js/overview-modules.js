/**
 * Core module toggles on Overview tab → POST /bankai/v1/core-module/{key}
 * Include after bankai-admin.js or merge into it.
 */
(function () {
    'use strict';

    function cfg() {
        return window.bankaiCoreData || window.bankaiData || {};
    }

    async function toggleCore(key, enabled) {
        const c = cfg();
        const base = (c.restUrl || '').replace(/\/$/, '');
        const res = await fetch(base + '/core-module/' + encodeURIComponent(key), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': c.nonce || ''
            },
            credentials: 'same-origin',
            body: JSON.stringify({ enabled: !!enabled })
        });
        const data = await res.json().catch(function () { return {}; });
        if (!res.ok || !data.success) {
            throw new Error((data && data.message) || res.statusText);
        }
        return data;
    }

    document.addEventListener('change', function (e) {
        const input = e.target;
        if (!input || !input.classList || !input.classList.contains('bk-core-module-toggle')) {
            return;
        }
        const key = input.getAttribute('data-module');
        const enabled = !!input.checked;
        input.disabled = true;
        toggleCore(key, enabled)
            .then(function (data) {
                const chip = document.getElementById('bk-core-mod-count');
                if (chip && typeof data.active_count === 'number') {
                    const parts = chip.childNodes;
                    // update leading number text roughly
                    chip.innerHTML = data.active_count + ' / 6 <span>' +
                        ((window.bankaiAdminInstance && window.bankaiAdminInstance.isRtl) ? 'فعال' : 'Active') +
                        '</span>';
                }
                if (window.showToast) {
                    window.showToast(data.message || 'OK', 'success');
                }
            })
            .catch(function (err) {
                input.checked = !enabled;
                if (window.showToast) {
                    window.showToast(err.message || 'Error', 'error');
                }
            })
            .finally(function () {
                input.disabled = false;
            });
    });
})();
