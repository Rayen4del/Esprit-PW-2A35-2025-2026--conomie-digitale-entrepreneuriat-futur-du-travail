/**
 * Comments Management - Backoffice
 * Isolated script to avoid conflicts with main menu.js
 */

'use strict';

// Use a different variable name to avoid conflicts
const COMMENT_CONTROLLER = window.COMMENT_CONTROLLER || '';

// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('Comments JS loaded');
    
    // Initialize Bootstrap tooltips if available
    if (typeof bootstrap !== 'undefined') {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.forEach(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
    
    // Initialize filter functionality
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    
    if (searchInput) {
        searchInput.addEventListener('input', filterTable);
    }
    if (statusFilter) {
        statusFilter.addEventListener('change', filterTable);
    }
});

// ── Status Update ──────────────────────────────────────────────
window.updateStatus = function(id, newStatus) {
    fetch(COMMENT_CONTROLLER, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=update_status&comment_id=' + id + '&status=' + newStatus
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            // Update badge
            const badge = document.getElementById('badge-' + id);
            const clsMap = { 
                approved: 'bg-label-success', 
                pending: 'bg-label-warning', 
                rejected: 'bg-label-danger' 
            };
            badge.className = 'badge ' + clsMap[newStatus];
            badge.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);

            // Update buttons disabled state in the row
            const row = document.getElementById('row-' + id);
            const buttons = row.querySelectorAll('button[onclick*="updateStatus"]');
            buttons.forEach(function(btn) {
                const onclickAttr = btn.getAttribute('onclick');
                if (onclickAttr) {
                    const match = onclickAttr.match(/updateStatus\(\d+,\s*'(\w+)'\)/);
                    if (match) {
                        const btnStatus = match[1];
                        btn.disabled = (btnStatus === newStatus);
                    }
                }
            });

            // Recalculate stats
            recalcStats();
            showToast('Status updated to ' + newStatus, 'success');
        } else {
            showToast(data.message || 'Update failed', 'error');
        }
    })
    .catch(function() { showToast('Network error', 'error'); });
};

// ── Delete ─────────────────────────────────────────────────────
window.deleteComment = function(id) {
    if (!confirm('Delete this comment permanently?')) return;
    
    fetch(COMMENT_CONTROLLER, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=backDelete&comment_id=' + id
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            const row = document.getElementById('row-' + id);
            if (row) row.remove();
            recalcStats();
            showToast('Comment deleted', 'success');
        } else {
            showToast(data.message || 'Delete failed', 'error');
        }
    })
    .catch(function() { showToast('Network error', 'error'); });
};

// ── Filter ─────────────────────────────────────────────────────
window.filterTable = function() {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    
    if (!searchInput || !statusFilter) return;
    
    const search = searchInput.value.toLowerCase();
    const statusF = statusFilter.value.toLowerCase();
    
    const rows = document.querySelectorAll('#commentsTable tbody tr[id^="row-"]');
    rows.forEach(function(row) {
        const text = row.textContent.toLowerCase();
        const badge = row.querySelector('.badge');
        const st = badge ? badge.textContent.trim().toLowerCase() : '';
        const matchesSearch = text.indexOf(search) !== -1;
        const matchesStatus = !statusF || st === statusF;
        
        row.style.display = (matchesSearch && matchesStatus) ? '' : 'none';
    });
};

// ── Recalc stat counters from DOM ──────────────────────────────
function recalcStats() {
    let total = 0, pending = 0, approved = 0, rejected = 0;
    
    const rows = document.querySelectorAll('#commentsTable tbody tr[id^="row-"]');
    rows.forEach(function(row) {
        total++;
        const badge = row.querySelector('.badge');
        const st = badge ? badge.textContent.trim().toLowerCase() : 'pending';
        if (st === 'pending') pending++;
        else if (st === 'approved') approved++;
        else if (st === 'rejected') rejected++;
    });
    
    const statTotal = document.getElementById('stat-total');
    const statPending = document.getElementById('stat-pending');
    const statApproved = document.getElementById('stat-approved');
    const statRejected = document.getElementById('stat-rejected');
    
    if (statTotal) statTotal.textContent = total;
    if (statPending) statPending.textContent = pending;
    if (statApproved) statApproved.textContent = approved;
    if (statRejected) statRejected.textContent = rejected;
}

// ── Toast Notification ──────────────────────────────────────────
function showToast(msg, type) {
    type = type || 'success';
    const container = document.getElementById('toast-container');
    if (!container) return;
    
    const toast = document.createElement('div');
    toast.className = 'toast-msg ' + type;
    toast.textContent = msg;
    container.appendChild(toast);
    
    setTimeout(function() {
        if (toast.remove) toast.remove();
    }, 3000);
}