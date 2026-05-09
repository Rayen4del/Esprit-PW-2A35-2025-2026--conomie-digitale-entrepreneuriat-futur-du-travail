// ─────────────────────────────────────────────
// SAVE POST
// ─────────────────────────────────────────────
function toggleSave(postId) {
    const userId = 1;
    fetch('index.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=save_post&post_id=${postId}&user_id=${userId}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.querySelectorAll(`.save-btn[data-post-id="${postId}"] i`).forEach(icon => {
                icon.className = `bx ${data.saved ? 'bxs-bookmark' : 'bx-bookmark'}`;
            });
            // Also update dropdown text if present
            document.querySelectorAll(`.dropdown-menu .dropdown-item[onclick*='toggleSave(${postId})']`).forEach(item => {
                item.innerHTML = `<i class='bx ${data.saved ? 'bxs-bookmark' : 'bx-bookmark'} me-2'></i> ${data.saved ? 'Retirer' : 'Enregistrer'}`;
            });
        }
    })
    .catch(() => showNotification('Erreur lors de l&apos;enregistrement du post', 'error'));
}

// ─────────────────────────────────────────────
// SHARE POST
// ─────────────────────────────────────────────
function sharePost(postId) {
    const userId = 1;
    fetch('index.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=share_post&post_id=${postId}&user_id=${userId}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Copy link to clipboard
            navigator.clipboard.writeText(data.url);
            showNotification('Lien de partage copié !');
        }
    })
    .catch(() => showNotification('Erreur lors du partage du post', 'error'));
}
// front_office/posts/js/feed.js

// ─────────────────────────────────────────────
// LIKE
// ─────────────────────────────────────────────
function toggleLike(postId) {
    const userId = 1;
    fetch('index.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=like_post&post_id=${postId}&user_id=${userId}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const likeBtn       = document.querySelector(`.like-btn[data-post-id="${postId}"]`);
            const heartIcon     = likeBtn?.querySelector('i');
            const likeCountSpan = likeBtn?.querySelector('.like-count');
            if (heartIcon) {
                heartIcon.className   = `bx ${data.liked ? 'bxs-heart' : 'bx-heart'}`;
                heartIcon.style.color = data.liked ? '#dc3545' : '';
            }
            if (likeCountSpan) likeCountSpan.textContent = data.count;
            if (likeBtn) {
                likeBtn.style.transform = 'scale(1.1)';
                setTimeout(() => { likeBtn.style.transform = 'scale(1)'; }, 200);
            }
        }
    })
    .catch(() => showNotification('Erreur lors du like du post', 'error'));
}

// ─────────────────────────────────────────────
// COMMENTS TOGGLE
// ─────────────────────────────────────────────
function toggleComments(postId) {
    const section = document.getElementById('comments-' + postId);
    if (!section) return;
    const isVisible = section.style.display === 'block';
    section.style.display = isVisible ? 'none' : 'block';
    if (!isVisible && typeof loadComments === 'function') loadComments(postId);
}

// ─────────────────────────────────────────────
// DELETE POST
// ─────────────────────────────────────────────
function deletePost(postId) {
    if (!confirm('Voulez-vous vraiment supprimer cette publication ?\n\nCette action est irréversible pour vous.')) {
        return;
    }

    fetch('index.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `action=delete_post&post_id=${postId}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Remove the post card from the UI
            const postCard = document.querySelector(`.post-card[data-post-id="${postId}"]`);
            if (postCard) {
                postCard.style.transition = 'opacity 0.3s';
                postCard.style.opacity = '0';
                setTimeout(() => postCard.remove(), 300);
            }
            showNotification('Publication supprimée avec succès', 'success');
        } else {
            showNotification(data.message || 'Erreur lors de la suppression', 'error');
        }
    })
    .catch(err => {
        console.error(err);
        showNotification('Erreur de connexion', 'error');
    });
}
// ─────────────────────────────────────────────
// EDIT POST
// ─────────────────────────────────────────────
function editPost(postId) {
    const card    = document.querySelector(`.post-card[data-post-id="${postId}"]`);
    const title   = card?.querySelector('.post-title')?.innerText.trim()   || '';
    const content = card?.querySelector('.post-content')?.innerText.trim() || '';

    document.getElementById('editPostId').value      = postId;
    document.getElementById('editPostTitre').value   = title;
    document.getElementById('editPostContenu').value = content;

    const counter = document.getElementById('editTitreCounter');
    if (counter) counter.textContent = `${title.length}/50`;

    // Show current media
    const currentMediaEl = document.getElementById('editCurrentMedia');
    const imgEl = card?.querySelector('.post-media-img');
    const vidEl = card?.querySelector('.post-media-video source');

    if (currentMediaEl) {
        if (imgEl) {
            currentMediaEl.innerHTML = `
                <p class="text-muted small mb-1">Image actuelle :</p>
                <img src="${imgEl.src}" style="max-height:100px;border-radius:8px;border:1px solid #dee2e6;">
            `;
        } else if (vidEl) {
            currentMediaEl.innerHTML = `
                <p class="text-muted small mb-1">Vidéo actuelle :</p>
                <video src="${vidEl.src}" style="max-height:100px;border-radius:8px;" muted></video>
            `;
        } else {
            currentMediaEl.innerHTML = '<p class="text-muted small mb-0">Aucun média actuellement attaché.</p>';
        }
    }

    // Clear previous preview and file input
    const previewEl = document.getElementById('editMediaPreview');
    if (previewEl) previewEl.innerHTML = '';
    const fileInput = document.getElementById('editMediaInput');
    if (fileInput) fileInput.value = '';

    clearEditErrors();
    new bootstrap.Modal(document.getElementById('editPostModal')).show();
}

// ─────────────────────────────────────────────
// MEDIA PREVIEW (live before upload)
// ─────────────────────────────────────────────
function attachMediaPreview(inputId, previewId) {
    const input   = document.getElementById(inputId);
    const preview = document.getElementById(previewId);
    if (!input || !preview) return;

    input.addEventListener('change', function () {
        preview.innerHTML = '';
        const file = this.files[0];
        if (!file) return;

        const url     = URL.createObjectURL(file);
        const isVideo = file.type.startsWith('video/');
        const wrap    = document.createElement('div');
        wrap.className = 'media-preview-wrap';

        if (isVideo) {
            wrap.innerHTML = `
                <video src="${url}" controls style="max-height:160px;border-radius:8px;border:1px solid #dee2e6;display:block;"></video>
                <button type="button" class="remove-media" onclick="clearMediaInput('${inputId}','${previewId}')">✕</button>
            `;
        } else {
            wrap.innerHTML = `
                <img src="${url}" style="max-height:160px;border-radius:8px;border:1px solid #dee2e6;display:block;">
                <button type="button" class="remove-media" onclick="clearMediaInput('${inputId}','${previewId}')">✕</button>
            `;
        }
        preview.appendChild(wrap);
    });
}

function clearMediaInput(inputId, previewId) {
    const input = document.getElementById(inputId);
    if (input) input.value = '';
    const preview = document.getElementById(previewId);
    if (preview) preview.innerHTML = '';
}

// ─────────────────────────────────────────────
// VALIDATION HELPERS
// ─────────────────────────────────────────────
function showError(fieldId, message) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    field.classList.add('is-invalid');
    let feedback = field.nextElementSibling;
    if (!feedback?.classList.contains('invalid-feedback')) {
        feedback = document.createElement('div');
        feedback.className = 'invalid-feedback';
        field.parentNode.insertBefore(feedback, field.nextSibling);
    }
    feedback.textContent = message;
}

function clearError(fieldId) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    field.classList.remove('is-invalid');
    const feedback = field.nextElementSibling;
    if (feedback?.classList.contains('invalid-feedback')) feedback.textContent = '';
}

function clearEditErrors() {
    clearError('editPostTitre');
    clearError('editPostContenu');
}

// ─────────────────────────────────────────────
// CREATE FORM VALIDATION
// ─────────────────────────────────────────────
function validateCreateForm(e) {
    e.preventDefault();
    const titre   = document.getElementById('createPostTitre');
    const contenuHidden = document.getElementById('createPostContenu');
    let valid = true;

    clearError('createPostTitre');
    clearError('createPostContenu');

    if (!titre.value.trim()) {
        showError('createPostTitre', 'Le titre est requis.');
        valid = false;
    } else if (titre.value.trim().length > 50) {
        showError('createPostTitre', 'Le titre doit contenir 50 caractères ou moins.');
        valid = false;
    }

    // Get Quill editor content
    const quillContent = quillCreateEditor.root.innerHTML.trim();
    const plainText = quillCreateEditor.getText().trim();

    if (!plainText) {
        showError('createPostContenu', 'Le contenu est requis.');
        valid = false;
    }

    if (valid) {
        // Copy Quill content to hidden input before submission
        contenuHidden.value = quillContent;
        e.target.submit();
    }
}

// ─────────────────────────────────────────────
// EDIT FORM VALIDATION
// ─────────────────────────────────────────────
function validateEditForm(e) {
    e.preventDefault();
    const titre   = document.getElementById('editPostTitre');
    const contenu = document.getElementById('editPostContenu');
    let valid = true;

    clearEditErrors();

    if (!titre.value.trim()) {
        showError('editPostTitre', 'Le titre est requis.');
        valid = false;
    } else if (titre.value.trim().length > 50) {
        showError('editPostTitre', 'Le titre doit contenir 50 caractères ou moins.');
        valid = false;
    }

    if (!contenu.value.trim()) {
        showError('editPostContenu', 'Le contenu est requis.');
        valid = false;
    }

    if (valid) e.target.submit();
}

// ─────────────────────────────────────────────
// LIVE CHAR COUNTER
// ─────────────────────────────────────────────
function attachCharCounter(inputId, counterId) {
    const input   = document.getElementById(inputId);
    const counter = document.getElementById(counterId);
    if (!input || !counter) return;
    input.addEventListener('input', () => {
        const len = input.value.length;
        counter.textContent = `${len}/50`;
        counter.style.color = len > 50 ? '#dc3545' : '#6c757d';
    });
}

// ─────────────────────────────────────────────
// TOAST NOTIFICATION
// ─────────────────────────────────────────────
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

// ─────────────────────────────────────────────
// DOM READY
// ─────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {

    // Search on Enter
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keypress', e => {
            if (e.key === 'Enter')
                window.location.href = 'index.php?search=' + encodeURIComponent(searchInput.value);
        });
    }

    // Flash messages
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('deleted') === 'success') {
        showNotification('Publication supprimée avec succès !', 'success');
        window.history.replaceState({}, document.title, window.location.pathname);
    }
    if (urlParams.get('msg') === 'updated') {
        showNotification('Publication mise à jour avec succès !', 'success');
        window.history.replaceState({}, document.title, window.location.pathname);
    }

    // Form validation
    const createForm = document.getElementById('createPostForm');
    if (createForm) createForm.addEventListener('submit', validateCreateForm);

    const editForm = document.getElementById('editPostForm');
    if (editForm) editForm.addEventListener('submit', validateEditForm);

    // Char counters
    attachCharCounter('createPostTitre', 'createTitreCounter');
    attachCharCounter('editPostTitre',   'editTitreCounter');

    // Media previews
    attachMediaPreview('createMediaInput', 'createMediaPreview');
    attachMediaPreview('editMediaInput',   'editMediaPreview');
});