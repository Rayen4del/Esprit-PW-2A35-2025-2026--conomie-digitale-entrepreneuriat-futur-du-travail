// front_office/comments/js/comments.js

function loadComments(postId) {
    fetch(`../../../../../controller/Commentcontroller.php?action=get_comments&post_id=${postId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayComments(postId, data.comments);
            }
        })
        .catch(error => console.error('Error loading comments:', error));
}

function displayComments(postId, comments) {
    const commentsContainer = document.getElementById(`comments-list-${postId}`);
    if (!commentsContainer) return;
    
    const countSpan = document.querySelector(`.comment-count-${postId}`);
    const countDisplay = document.querySelector(`.comment-count-display-${postId}`);
    if (countSpan) countSpan.textContent = comments.length;
    if (countDisplay) countDisplay.textContent = comments.length;
    
    if (comments.length === 0) {
        commentsContainer.innerHTML = '<p class="text-muted small text-center">No comments yet. Be the first to comment!</p>';
        return;
    }
    
    let html = '';
    for (let comment of comments) {
        const date = new Date(comment.DateCom).toLocaleDateString('en-US', { 
            month: 'short', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        
        html += `
            <div class="comment-item mb-3" data-comment-id="${comment.ID}">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="d-flex gap-2 flex-grow-1">
                        <div class="avatar-sm">
                            <span class="avatar-initial rounded-circle bg-label-secondary" style="font-size: 12px; display: flex; align-items: center; justify-content: center; width: 32px; height: 32px;">
                                ${escapeHtml((comment.auteur || 'U').charAt(0).toUpperCase())}
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <div class="bg-light p-2 rounded" style="background: #f0f2f5 !important;">
                                <strong class="small">${escapeHtml(comment.auteur || 'Anonymous')}</strong>
                                <p class="mb-0 small">${escapeHtml(comment.Contenu).replace(/\n/g, '<br>')}</p>
                            </div>
                            <div class="mt-1">
                                <small class="text-muted">${date}</small>
                                <small class="comment-like-btn ms-2" onclick="toggleCommentLike(${comment.ID}, ${postId})" style="cursor:pointer; color: ${comment.user_liked ? '#dc3545' : '#6c757d'}">
                                    <i class="bx ${comment.user_liked ? 'bxs-heart' : 'bx-heart'} bx-xs"></i>
                                    <span class="comment-like-count-${comment.ID}">${comment.like_count}</span>
                                </small>
                                ${comment.IDUtilisateur == 1 ? `<small class="text-danger ms-2" onclick="deleteComment(${comment.ID}, ${postId})" style="cursor:pointer;">Delete</small>` : ''}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    commentsContainer.innerHTML = html;
}

function submitComment(event, postId) {
    event.preventDefault();
    const form = event.target;
    const contentInput = form.querySelector('input[name="content"]');
    const content = contentInput.value.trim();
    const userId = 1;
    
    if (!content) {
        showNotification('Please enter a comment', 'warning');
        return;
    }
    
    fetch('../../../../../controller/Commentcontroller.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=create_comment&post_id=${postId}&user_id=${userId}&content=${encodeURIComponent(content)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            contentInput.value = '';
            showNotification('Comment added!', 'success');
            loadComments(postId);
        } else {
            showNotification(data.message || 'Error adding comment', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error adding comment', 'error');
    });
}

function deleteComment(commentId, postId) {
    if (confirm('Are you sure you want to delete this comment?')) {
        const userId = 1;
        
        fetch('../../../../../controller/Commentcontroller.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=delete_comment&comment_id=${commentId}&user_id=${userId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Comment deleted!', 'success');
                loadComments(postId);
            } else {
                showNotification(data.message || 'Error deleting comment', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error deleting comment', 'error');
        });
    }
}

function toggleCommentLike(commentId, postId) {
    const userId = 1;
    
    fetch('../../../../../controller/Commentcontroller.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=like_comment&comment_id=${commentId}&user_id=${userId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const likeSpan = document.querySelector(`.comment-like-count-${commentId}`);
            const likeBtn = likeSpan ? likeSpan.parentElement : null;
            
            if (likeSpan) {
                likeSpan.textContent = data.count;
            }
            
            if (likeBtn) {
                const icon = likeBtn.querySelector('i');
                if (data.liked) {
                    icon.className = 'bx bxs-heart';
                    likeBtn.style.color = '#dc3545';
                } else {
                    icon.className = 'bx bx-heart';
                    likeBtn.style.color = '#6c757d';
                }
                
                likeBtn.style.transform = 'scale(1.1)';
                setTimeout(() => {
                    if (likeBtn) likeBtn.style.transform = 'scale(1)';
                }, 200);
            }
        }
    })
    .catch(error => console.error('Error liking comment:', error));
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showNotification(message, type = 'success') {
    const existingNotification = document.querySelector('.toast-notification');
    if (existingNotification) {
        existingNotification.remove();
    }
    
    const notification = document.createElement('div');
    notification.textContent = message;
    notification.className = `toast-notification toast-${type}`;
    
    let backgroundColor = '#28a745';
    if (type === 'error') backgroundColor = '#dc3545';
    else if (type === 'info') backgroundColor = '#17a2b8';
    else if (type === 'warning') backgroundColor = '#ffc107';
    
    notification.style.backgroundColor = backgroundColor;
    notification.style.color = type === 'warning' ? '#333' : 'white';
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.padding = '12px 24px';
    notification.style.borderRadius = '8px';
    notification.style.zIndex = '9999';
    notification.style.fontWeight = 'bold';
    notification.style.boxShadow = '0 2px 10px rgba(0,0,0,0.2)';
    notification.style.animation = 'slideIn 0.3s ease';
    notification.style.fontSize = '14px';
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification && notification.remove) {
            notification.remove();
        }
    }, 3000);
}