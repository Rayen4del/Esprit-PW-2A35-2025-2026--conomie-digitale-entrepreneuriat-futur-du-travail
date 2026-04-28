/**
 * common.js
 * Utilitaires partagés entre toutes les vues EventHub.
 * Dépendances : Bootstrap 5, Bootstrap Icons
 */

'use strict';

// ─── Sidebar ────────────────────────────────────────────────────────────────
function toggleMenu(el) {
  el.closest('.menu-item').classList.toggle('open');
}

// ─── Toast ──────────────────────────────────────────────────────────────────
/**
 * Affiche un toast de feedback en bas à droite de l'écran.
 * @param {string} msg   Message à afficher
 * @param {'success'|'warning'|'error'} type
 */
function showToast(msg, type = 'success') {
  const area = document.getElementById('toast-area');
  if (!area) return;

  const colors = { success: '#696cff', warning: '#ffab00', error: '#ff3e1d' };
  const icons  = {
    success: 'bi-check-circle-fill',
    warning: 'bi-save-fill',
    error:   'bi-x-circle-fill',
  };

  const toast = document.createElement('div');
  toast.style.cssText = [
    'background:#fff',
    `border-left:4px solid ${colors[type]}`,
    'border-radius:.5rem',
    'padding:.75rem 1rem',
    'box-shadow:0 4px 20px rgba(67,89,113,.15)',
    'display:flex',
    'align-items:center',
    'gap:.6rem',
    'font-size:.875rem',
    'color:#566a7f',
    'min-width:260px',
    'animation:slideIn .3s ease',
  ].join(';');

  toast.innerHTML =
    `<i class="bi ${icons[type]}" style="color:${colors[type]};font-size:1.1rem"></i>${msg}`;

  area.appendChild(toast);
  setTimeout(() => toast.remove(), 3500);
}

// ─── Statut hint (formulaire ajout & modal édition) ─────────────────────────
/**
 * Met à jour le texte d'indication sous le select Statut.
 * @param {HTMLSelectElement} sel
 * @param {string} hintId  id de l'élément cible (défaut : 'statut-hint')
 */
function updateStatutColor(sel, hintId = 'statut-hint') {
  const hints = {
    ouvert:  '✅ Les inscriptions sont ouvertes.',
    ferme:   '🔒 Les inscriptions sont fermées.',
    complet: '⚠️ Plus aucune place disponible.',
  };
  const el = document.getElementById(hintId);
  if (el) el.textContent = hints[sel.value] || '';
}

// ─── Année courante dans le footer ─────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  const yearEl = document.getElementById('year');
  if (yearEl) yearEl.textContent = new Date().getFullYear();
});
