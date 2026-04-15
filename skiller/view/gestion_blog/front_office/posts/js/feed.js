// front_office/posts/js/feed.js

function toggleLike(postId) {
    const userId = 1;
    
    fetch('index.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=like_post&post_id=${postId}&user_id=${userId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const likeBtn = document.querySelector(`.like-btn[data-post-id="${postId}"]`);
            if (likeBtn) {
                const heartIcon = likeBtn.querySelector('i');
                const likeCountSpan = likeBtn.querySelector('.like-count');
                
                if (data.liked) {
                    heartIcon.className = 'bx bxs-heart';
                    heartIcon.style.color = '#dc3545';
                } else {
                    heartIcon.className = 'bx bx-heart';
                    heartIcon.style.color = '';
                }
                
                likeCountSpan.textContent = data.count;
                
                likeBtn.style.transform = 'scale(1.1)';
                setTimeout(() => {
                    likeBtn.style.transform = 'scale(1)';
                }, 200);
            } else {
                window.location.reload();
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error liking post', 'error');
    });
}

function toggleComments(postId) {
    const section = document.getElementById('comments-' + postId);
    if (section) {
        const isVisible = section.style.display === 'block';
        section.style.display = isVisible ? 'none' : 'block';
        
        if (!isVisible && typeof loadComments === 'function') {
            loadComments(postId);
        }
    }
}

function deletePost(postId) {
    if (confirm('Are you sure you want to delete this post permanently?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'index.php';
        
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'delete_post';
        input.value = postId;
        form.appendChild(input);
        
        document.body.appendChild(form);
        form.submit();
    }
}

function editPost(postId) {
    showNotification('Edit functionality coming soon! Post ID: ' + postId, 'info');
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

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                window.location.href = 'index.php?search=' + encodeURIComponent(this.value);
            }
        });
    }
    
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('deleted') === 'success') {
        showNotification('Post deleted successfully!', 'success');
        const newUrl = window.location.pathname;
        window.history.replaceState({}, document.title, newUrl);
    }
});