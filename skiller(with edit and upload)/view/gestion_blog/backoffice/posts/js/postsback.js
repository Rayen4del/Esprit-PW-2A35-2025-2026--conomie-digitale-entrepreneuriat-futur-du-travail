/**
 * Posts Management - Backoffice
 * Handles post deletion and status changes via AJAX
 */

'use strict';

// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('Posts JS loaded - Initializing...');
    
    // Controller URL
    window.POSTS_CONTROLLER = '/skiller/controller/PostController.php';
    
    // Initialize all functionality
    initDeleteHandler();
    initStatusHandler();
    initModalHandler();
});

// Initialize delete button handler
function initDeleteHandler() {
    // Use event delegation for dynamic elements
    document.body.addEventListener('click', function(e) {
        // Check if clicked element is a delete button or inside one
        const deleteBtn = e.target.closest('.delete-post-btn');
        if (deleteBtn) {
            e.preventDefault();
            const postId = deleteBtn.getAttribute('data-id');
            const postTitle = deleteBtn.getAttribute('data-title');
            
            // Show modal with post info
            showDeleteModal(postId, postTitle);
        }
    });
}

// Initialize status change handler
function initStatusHandler() {
    // Use event delegation for status selects
    document.body.addEventListener('change', function(e) {
        const statusSelect = e.target.closest('.status-select');
        if (statusSelect) {
            e.preventDefault();
            const postId = statusSelect.getAttribute('data-post-id');
            const newStatus = statusSelect.value;
            const originalValue = statusSelect.getAttribute('data-original-value');
            
            // Update status via AJAX
            updatePostStatus(postId, newStatus, statusSelect, originalValue);
        }
    });
}

// Initialize modal handler for confirm button
function initModalHandler() {
    // Wait for modal to be ready
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    if (confirmBtn) {
        // Remove any existing listeners
        const newConfirmBtn = confirmBtn.cloneNode(true);
        confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
        
        newConfirmBtn.addEventListener('click', function() {
            const postId = document.getElementById('deletePostId').value;
            if (postId) {
                executeDeletePost(postId);
            }
        });
    }
}

// Show delete confirmation modal
function showDeleteModal(postId, postTitle) {
    const deletePostId = document.getElementById('deletePostId');
    const deletePostTitle = document.getElementById('deletePostTitle');
    
    if (deletePostId) deletePostId.value = postId;
    if (deletePostTitle) deletePostTitle.textContent = '"' + postTitle + '"';
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

// Execute post deletion
function executeDeletePost(postId) {
    console.log('Deleting post:', postId);
    
    // Show loading toast
    showToast('Deleting post...', 'info');
    
    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('deleteModal'));
    if (modal) modal.hide();
    
    // Send AJAX request
    fetch(window.POSTS_CONTROLLER, {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'action=backDelete&id=' + postId
    })
    .then(function(response) { 
        return response.json(); 
    })
    .then(function(data) {
        console.log('Response:', data);
        if (data.success) {
            showToast('Post deleted successfully!', 'success');
            // Remove the row from the table
            const row = document.getElementById('post-row-' + postId);
            if (row) {
                row.remove();
                updateStats();
                updateRowNumbers();
            }
        } else {
            showToast(data.message || 'Delete failed', 'error');
        }
    })
    .catch(function(error) {
        console.error('Error:', error);
        showToast('Network error occurred', 'error');
    });
}

// Update post status via AJAX
function updatePostStatus(postId, newStatus, selectElement, originalValue) {
    console.log('Updating status:', postId, 'to', newStatus);
    
    // Disable select while processing
    selectElement.disabled = true;
    
    fetch(window.POSTS_CONTROLLER, {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'action=backChangeStatus&id=' + postId + '&statut=' + encodeURIComponent(newStatus)
    })
    .then(function(response) { 
        return response.json(); 
    })
    .then(function(data) {
        console.log('Status update response:', data);
        if (data.success) {
            showToast('Status updated successfully', 'success');
            // Update the original value attribute
            selectElement.setAttribute('data-original-value', newStatus);
            // Update stats
            updateStats();
        } else {
            showToast(data.message || 'Status update failed', 'error');
            // Revert select to original value
            selectElement.value = originalValue;
        }
    })
    .catch(function(error) {
        console.error('Error:', error);
        showToast('Network error occurred', 'error');
        // Revert select to original value
        selectElement.value = originalValue;
    })
    .finally(function() {
        selectElement.disabled = false;
    });
}

// Update statistics after deletion or status change
function updateStats() {
    const rows = document.querySelectorAll('.table tbody tr');
    let total = rows.length;
    let published = 0, drafts = 0, archived = 0;
    
    rows.forEach(function(row) {
        const statusSelect = row.querySelector('.status-select');
        if (statusSelect) {
            const status = statusSelect.value;
            if (status === 'publié') {
                published++;
            } else if (status === 'brouillon') {
                drafts++;
            } else if (status === 'archivé') {
                archived++;
            }
        }
    });
    
    // Update stats display
    const statTotal = document.querySelector('.stats-strip .card:first-child .fw-bold');
    const statPublished = document.querySelector('.stats-strip .card:nth-child(2) .fw-bold');
    const statDrafts = document.querySelector('.stats-strip .card:nth-child(3) .fw-bold');
    const statArchived = document.querySelector('.stats-strip .card:nth-child(4) .fw-bold');
    
    if (statTotal) statTotal.textContent = total;
    if (statPublished) statPublished.textContent = published;
    if (statDrafts) statDrafts.textContent = drafts;
    if (statArchived) statArchived.textContent = archived;
}

// Update row numbers after deletion
function updateRowNumbers() {
    const rows = document.querySelectorAll('.table tbody tr');
    rows.forEach(function(row, index) {
        const firstCell = row.querySelector('td:first-child');
        if (firstCell) {
            firstCell.textContent = index + 1;
        }
    });
}

// Toast notification
function showToast(msg, type) {
    type = type || 'success';
    
    // Create toast container if it doesn't exist
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.style.cssText = 'position:fixed;bottom:1.5rem;right:1.5rem;z-index:9999;display:flex;flex-direction:column;gap:8px;';
        document.body.appendChild(container);
    }
    
    const toast = document.createElement('div');
    toast.className = 'toast-msg';
    toast.style.cssText = 'padding:0.75rem 1.25rem;border-radius:8px;color:#fff;font-size:0.875rem;box-shadow:0 4px 12px rgba(0,0,0,0.15);animation:slideIn 0.3s ease;';
    
    if (type === 'success') {
        toast.style.background = '#71dd37';
        toast.style.color = '#333';
    } else if (type === 'error') {
        toast.style.background = '#ff3e1d';
    } else if (type === 'info') {
        toast.style.background = '#03c3ec';
    }
    
    toast.textContent = msg;
    container.appendChild(toast);
    
    setTimeout(function() {
        if (toast.remove) toast.remove();
    }, 3000);
}

// Add CSS animation if not present
if (!document.querySelector('#toast-styles')) {
    const style = document.createElement('style');
    style.id = 'toast-styles';
    style.textContent = `
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    `;
    document.head.appendChild(style);
}