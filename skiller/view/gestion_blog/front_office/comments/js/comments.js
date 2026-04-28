// front_office/comments/js/comments.js

function loadComments(postId) {
    fetch(`${COMMENT_CONTROLLER}?action=get_comments&post_id=${postId}`)
        .then(r => r.json())
        .then(data => {
            if (data.success) displayComments(postId, data.comments);
        })
        .catch(error => console.error('Error loading comments:', error));
}

function displayComments(postId, comments) {
    const container = document.getElementById(`comments-list-${postId}`);
    if (!container) return;

    const countSpan   = document.querySelector(`.comment-count-${postId}`);
    const countDisplay = document.querySelector(`.comment-count-display-${postId}`);
    if (countSpan)    countSpan.textContent    = comments.length;
    if (countDisplay) countDisplay.textContent = comments.length;

    if (comments.length === 0) {
        container.innerHTML = '<p class="text-muted small text-center">No comments yet. Be the first to comment!</p>';
        return;
    }

    let html = '';
    for (let comment of comments) {
        const date = new Date(comment.DateCom).toLocaleDateString('en-US', {
            month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
        });

        const isOwner = comment.IDUtilisateur == 1;

        html += `
            <div class="comment-item mb-3" data-comment-id="${comment.ID}">
                <div class="d-flex gap-2 flex-grow-1">
                    <div class="avatar-sm">
                        <span class="avatar-initial rounded-circle bg-label-secondary"
                              style="font-size:12px;display:flex;align-items:center;justify-content:center;width:32px;height:32px;">
                            ${escapeHtml((comment.auteur || 'U').charAt(0).toUpperCase())}
                        </span>
                    </div>
                    <div class="flex-grow-1">
                        <!-- View mode -->
                        <div class="comment-view-${comment.ID}">
                            <div class="bg-light p-2 rounded" style="background:#f0f2f5 !important;">
                                <strong class="small">${escapeHtml(comment.auteur || 'Anonymous')}</strong>
                                <p class="mb-0 small comment-text-${comment.ID}">${escapeHtml(comment.Contenu).replace(/\n/g, '<br>')}</p>
                            </div>
                            <div class="mt-1 d-flex align-items-center gap-2">
                                <small class="text-muted">${date}</small>
                                <small class="comment-like-btn"
                                       onclick="toggleCommentLike(${comment.ID}, ${postId})"
                                       style="cursor:pointer;color:${comment.user_liked ? '#dc3545' : '#6c757d'}">
                                    <i class="bx ${comment.user_liked ? 'bxs-heart' : 'bx-heart'} bx-xs"></i>
                                    <span class="comment-like-count-${comment.ID}">${comment.like_count}</span>
                                </small>
                                ${isOwner ? `
                                <small class="text-primary ms-1" onclick="startEditComment(${comment.ID}, ${postId})" style="cursor:pointer;">
                                    <i class="bx bx-edit-alt bx-xs"></i> Edit
                                </small>
                                <small class="text-danger ms-1" onclick="deleteComment(${comment.ID}, ${postId})" style="cursor:pointer;">
                                    <i class="bx bx-trash bx-xs"></i> Delete
                                </small>` : ''}
                            </div>
                        </div>

                        <!-- Edit mode (hidden by default) -->
                        <div class="comment-edit-${comment.ID}" style="display:none;">
                            <div class="d-flex gap-2 mt-1">
                                <input type="text"
                                       id="edit-input-${comment.ID}"
                                       class="form-control form-control-sm"
                                       value="${escapeHtml(comment.Contenu)}">
                                <button class="btn btn-sm btn-primary" onclick="submitEditComment(${comment.ID}, ${postId})">
                                    Save
                                </button>
                                <button class="btn btn-sm btn-outline-secondary" onclick="cancelEditComment(${comment.ID})">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    container.innerHTML = html;
}

// ── EDIT HELPERS ────────────────────────────────────────────────

function startEditComment(commentId, postId) {
    document.querySelector(`.comment-view-${commentId}`).style.display = 'none';
    document.querySelector(`.comment-edit-${commentId}`).style.display = 'block';
    // Focus the input
    const input = document.getElementById(`edit-input-${commentId}`);
    if (input) { input.focus(); input.select(); }
}

function cancelEditComment(commentId) {
    document.querySelector(`.comment-view-${commentId}`).style.display = 'block';
    document.querySelector(`.comment-edit-${commentId}`).style.display = 'none';
}

function submitEditComment(commentId, postId) {
    const input   = document.getElementById(`edit-input-${commentId}`);
    const content = input ? input.value.trim() : '';
    const userId  = 1;

    if (!content) {
        showNotification('Comment cannot be empty', 'warning');
        return;
    }

    fetch(COMMENT_CONTROLLER, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=edit_comment&comment_id=${commentId}&user_id=${userId}&content=${encodeURIComponent(content)}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Update the displayed text without reloading all comments
            const textEl = document.querySelector(`.comment-text-${commentId}`);
            if (textEl) textEl.innerHTML = escapeHtml(content).replace(/\n/g, '<br>');
            // Also update the edit input value in case they edit again
            if (input) input.value = content;
            cancelEditComment(commentId);
            showNotification('Comment updated!', 'success');
        } else {
            showNotification(data.message || 'Failed to update comment', 'error');
        }
    })
    .catch(() => showNotification('Error updating comment', 'error'));
}

// ── SUBMIT NEW COMMENT ───────────────────────────────────────────

function submitComment(event, postId) {
    event.preventDefault();
    const form         = event.target;
    const contentInput = form.querySelector('input[name="content"]');
    const content      = contentInput.value.trim();
    const userId       = 1;

    if (!content) {
        showNotification('Please enter a comment', 'warning');
        return;
    }

    fetch(COMMENT_CONTROLLER, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=create_comment&post_id=${postId}&user_id=${userId}&content=${encodeURIComponent(content)}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            contentInput.value = '';
            showNotification('Comment added!', 'success');
            loadComments(postId);
        } else {
            showNotification(data.message || 'Error adding comment', 'error');
        }
    })
    .catch(() => showNotification('Error adding comment', 'error'));
}

// ── DELETE ──────────────────────────────────────────────────────

function deleteComment(commentId, postId) {
    if (!confirm('Are you sure you want to delete this comment?')) return;
    const userId = 1;

    fetch(COMMENT_CONTROLLER, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=delete_comment&comment_id=${commentId}&user_id=${userId}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showNotification('Comment deleted!', 'success');
            loadComments(postId);
        } else {
            showNotification(data.message || 'Error deleting comment', 'error');
        }
    })
    .catch(() => showNotification('Error deleting comment', 'error'));
}

// ── LIKE ────────────────────────────────────────────────────────

function toggleCommentLike(commentId, postId) {
    const userId = 1;

    fetch(COMMENT_CONTROLLER, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=like_comment&comment_id=${commentId}&user_id=${userId}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const likeSpan = document.querySelector(`.comment-like-count-${commentId}`);
            const likeBtn  = likeSpan ? likeSpan.closest('.comment-like-btn') : null;
            if (likeSpan) likeSpan.textContent = data.count;
            if (likeBtn) {
                const icon = likeBtn.querySelector('i');
                icon.className      = `bx ${data.liked ? 'bxs-heart' : 'bx-heart'} bx-xs`;
                likeBtn.style.color = data.liked ? '#dc3545' : '#6c757d';
                likeBtn.style.transform = 'scale(1.1)';
                setTimeout(() => { likeBtn.style.transform = 'scale(1)'; }, 200);
            }
        }
    })
    .catch(error => console.error('Error liking comment:', error));
}

// ── UTILS ────────────────────────────────────────────────────────

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showNotification(message, type = 'success') {
    const existing = document.querySelector('.toast-notification');
    if (existing) existing.remove();

    const n = document.createElement('div');
    n.textContent = message;
    n.className   = `toast-notification toast-${type}`;
    Object.assign(n.style, {
        position: 'fixed', top: '20px', right: '20px',
        padding: '12px 24px', borderRadius: '8px', zIndex: '9999',
        fontWeight: 'bold', boxShadow: '0 2px 10px rgba(0,0,0,0.2)',
        fontSize: '14px', color: type === 'warning' ? '#333' : 'white',
        background: type === 'error'   ? '#dc3545' :
                    type === 'info'    ? '#17a2b8' :
                    type === 'warning' ? '#ffc107' : '#28a745'
    });
    document.body.appendChild(n);
    setTimeout(() => n.remove(), 3000);
}