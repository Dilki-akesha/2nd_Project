// ============================================================ 
// COVERAGE ROUTES — CRUD via AJAX + Database (Complete)
// ============================================================ 

(function() {
    'use strict';

    const CONTROLLER_URL = '../../Controller/Delivery/CoverageController.php';

    const modal = document.getElementById('routeModal');
    const tableBody = document.getElementById('routesTableBody');
    if (!modal || !tableBody) return;

    const titleEl = document.getElementById('routeModalTitle');
    const editIdInput = document.getElementById('routeEditId');
    const originSelect = document.getElementById('routeOrigin');
    const destSelect = document.getElementById('routeDestination');
    const statusSelect = document.getElementById('routeStatus');
    const errorEl = document.getElementById('routeFormError');
    const saveBtn = document.getElementById('saveRouteBtn');
    const saveBtnText = document.getElementById('saveRouteBtnText');
    const previewOrigin = document.getElementById('previewOrigin');
    const previewDest = document.getElementById('previewDestination');
    const routeCountEl = document.getElementById('routeCount');

    // ============================================================
    // HELPERS
    // ============================================================
    function getDistrictName(sel) {
        const o = sel.options[sel.selectedIndex];
        return o && o.dataset.name ? o.dataset.name : '';
    }

    function updateCount() {
        if (routeCountEl) {
            const count = tableBody.querySelectorAll('tr[data-route-id]').length;
            routeCountEl.textContent = count + ' routes total';
        }
    }

    // ============================================================
    // MODAL OPEN/CLOSE
    // ============================================================
    function openAddModal() {
        titleEl.innerHTML = '<i class="fas fa-route"></i> Add Coverage Route';
        saveBtnText.textContent = 'Save Route';
        editIdInput.value = '';
        originSelect.value = '';
        destSelect.value = '';
        statusSelect.value = 'active';
        errorEl.style.display = 'none';
        updatePreview();
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function openEditModal(id, oid, did, status) {
        titleEl.innerHTML = '<i class="fas fa-edit"></i> Edit Coverage Route';
        saveBtnText.textContent = 'Update Route';
        editIdInput.value = id;
        originSelect.value = oid;
        destSelect.value = did;
        statusSelect.value = status;
        errorEl.style.display = 'none';
        updatePreview();
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    window.closeRouteModal = function() {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    };

    function updatePreview() {
        previewOrigin.textContent = getDistrictName(originSelect) || 'Origin';
        previewDest.textContent = getDistrictName(destSelect) || 'Destination';
    }

    originSelect.addEventListener('change', updatePreview);
    destSelect.addEventListener('change', updatePreview);

    // ============================================================
    // VALIDATION
    // ============================================================
    function validate() {
        const oid = originSelect.value;
        const did = destSelect.value;
        const eid = editIdInput.value;
        if (!oid) return 'Please select an origin district.';
        if (!did) return 'Please select a destination district.';
        if (oid === did) return 'Origin and Destination cannot be the same.';
        for (const row of tableBody.querySelectorAll('tr')) {
            if (!row.dataset.routeId) continue;
            if (eid && row.dataset.routeId === eid) continue;
            if (row.dataset.originId === oid && row.dataset.destinationId === did) {
                return 'This route already exists. Duplicate routes are not allowed.';
            }
        }
        return null;
    }

    // ============================================================
    // SAVE (CREATE / UPDATE)
    // ============================================================
    saveBtn.addEventListener('click', function() {
        const err = validate();
        if (err) { errorEl.textContent = err; errorEl.style.display = 'block'; return; }
        errorEl.style.display = 'none';

        const eid = editIdInput.value;
        const formData = new FormData();
        formData.append('action', eid ? 'update' : 'create');
        if (eid) formData.append('route_id', eid);
        formData.append('origin_id', originSelect.value);
        formData.append('destination_id', destSelect.value);
        formData.append('is_active', statusSelect.value === 'active' ? '1' : '0');

        saveBtn.disabled = true;
        saveBtnText.textContent = 'Saving...';

        fetch(CONTROLLER_URL, { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                saveBtn.disabled = false;
                saveBtnText.textContent = eid ? 'Update Route' : 'Save Route';

                if (!data.success) {
                    errorEl.textContent = data.message || 'Save failed.';
                    errorEl.style.display = 'block';
                    return;
                }

                closeRouteModal();
                showToast(eid ? 'Route updated' : 'Route added');
                setTimeout(() => location.reload(), 600);
            })
            .catch(err => {
                saveBtn.disabled = false;
                saveBtnText.textContent = eid ? 'Update Route' : 'Save Route';
                errorEl.textContent = 'Network error. Please try again.';
                errorEl.style.display = 'block';
                console.error(err);
            });
    });

    // ============================================================
    // TOGGLE
    // ============================================================
    function toggleRow(row) {
        const routeId = row.dataset.routeId;
        const formData = new FormData();
        formData.append('action', 'toggle');
        formData.append('route_id', routeId);

        fetch(CONTROLLER_URL, { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                if (!data.success) {
                    alert(data.message || 'Toggle failed.');
                    return;
                }
                const active = data.is_active == 1;
                row.dataset.status = active ? 'active' : 'inactive';

                const b = row.querySelector('.route-status-badge');
                b.textContent = active ? 'Active' : 'Inactive';
                b.className = 'status-badge ' + (active ? 'completed' : 'pending') + ' route-status-badge';

                const t = row.querySelector('.toggle-route-btn');
                t.className = 'btn-sm ' + (active ? 'btn-warning' : 'btn-accept') + ' toggle-route-btn';
                t.innerHTML = `<i class="fas ${active ? 'fa-pause' : 'fa-play'}"></i> ${active ? 'Deactivate' : 'Reactivate'}`;

                showToast(`${row.dataset.origin} → ${row.dataset.destination} is now ${active ? 'Active' : 'Inactive'}`);
            })
            .catch(err => {
                alert('Network error.');
                console.error(err);
            });
    }

    // ============================================================
    // DELETE (FIXED)
    // ============================================================
    function deleteRow(row) {
        const routeId = row.dataset.routeId;
        const origin = row.dataset.origin;
        const destination = row.dataset.destination;

        console.log('🗑️ Delete clicked:', routeId, origin, destination);

        if (!confirm(`Delete route "${origin} → ${destination}"?\n\nThis action cannot be undone.`)) {
            return;
        }

        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('route_id', routeId);

        fetch(CONTROLLER_URL, { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                if (!data.success) {
                    alert(data.message || 'Delete failed.');
                    return;
                }

                // Remove row with animation
                row.style.transition = 'opacity 0.3s, transform 0.3s';
                row.style.opacity = '0';
                row.style.transform = 'translateX(-20px)';

                setTimeout(() => {
                    if (row.parentNode) row.parentNode.removeChild(row);
                    updateCount();
                    showToast(`Route ${origin} → ${destination} deleted`);
                }, 300);
            })
            .catch(err => {
                alert('Network error.');
                console.error(err);
            });
    }

    // ============================================================
    // ROW HANDLERS
    // ============================================================
    function attachHandlers(row) {
        row.querySelector('.edit-route-btn')?.addEventListener('click', () => {
            openEditModal(row.dataset.routeId, row.dataset.originId, row.dataset.destinationId, row.dataset.status);
        });
        row.querySelector('.toggle-route-btn')?.addEventListener('click', () => toggleRow(row));
        row.querySelector('.delete-route-btn')?.addEventListener('click', () => deleteRow(row));
    }

    tableBody.querySelectorAll('tr[data-route-id]').forEach(attachHandlers);

    // ============================================================
    // ADD BUTTON + ESC
    // ============================================================
    document.getElementById('addRouteBtn')?.addEventListener('click', openAddModal);
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && modal.style.display === 'flex') closeRouteModal();
    });

    // ============================================================
    // FILTERS
    // ============================================================
    const search = document.getElementById('routeSearch');
    const filter = document.getElementById('routeStatusFilter');
    function applyFilters() {
        const q = (search?.value || '').toLowerCase().trim();
        const s = filter?.value || 'all';
        tableBody.querySelectorAll('tr[data-route-id]').forEach(row => {
            const o = (row.dataset.origin || '').toLowerCase();
            const d = (row.dataset.destination || '').toLowerCase();
            const st = row.dataset.status;
            row.style.display = ((!q || o.includes(q) || d.includes(q)) && (s === 'all' || st === s)) ? '' : 'none';
        });
    }
    search?.addEventListener('input', applyFilters);
    filter?.addEventListener('change', applyFilters);

    // ============================================================
    // TOAST
    // ============================================================
    let toastTimer = null;
    function showToast(msg) {
        const t = document.getElementById('routeToast');
        const txt = document.getElementById('routeToastText');
        if (!t || !txt) return;
        txt.textContent = msg;
        t.classList.add('show');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => t.classList.remove('show'), 3000);
    }

    // Expose for debugging
    window.updateCount = updateCount;

    console.log('Coverage CRUD module loaded (complete)');
})();