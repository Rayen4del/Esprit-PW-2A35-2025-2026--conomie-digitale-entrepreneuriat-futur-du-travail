/**
 * form_validation.js
 * Validation côté client pour form_evenement.php (ajout d'un événement).
 *
 * Dépendances (dans l'ordre de chargement) :
 *   1. common.js
 *   2. validate_rules.js
 *   3. ce fichier
 */

'use strict';

// ─── Cartographie champ → ids ────────────────────────────────────────────────
// fieldName (EVENT_RULES key) → { errDivId, inputId }
const ADD_FIELD_MAP = [
  { name: 'titre',     errDivId: 'err-titre',  inputId: 'event-titre'  },
  { name: 'type',      errDivId: 'err-type',   inputId: 'event-type'   },
  { name: 'dateEvent', errDivId: 'err-date',   inputId: 'event-date'   },
  { name: 'duree',     errDivId: 'err-duree',  inputId: 'event-duree'  },
  { name: 'lieu_lien', errDivId: 'err-lieu',   inputId: 'event-lieu'   },
  { name: 'nbplaces',  errDivId: 'err-places', inputId: 'event-places' },
  { name: 'statut',    errDivId: 'err-statut', inputId: 'event-statut' },
];

// ─── Validation au blur (champ par champ) ───────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  ADD_FIELD_MAP.forEach(({ name, errDivId, inputId }) => {
    const el = document.getElementById(inputId);
    if (!el) return;

    el.addEventListener('blur', () => {
      validateField(name, el.value.trim(), errDivId, inputId);
    });

    // Efface l'erreur dès que l'utilisateur recommence à saisir
    el.addEventListener('input', () => {
      clearError(errDivId, inputId);
    });
  });

  // ─── Validation complète à la soumission ──────────────────────────────────
  const form = document.getElementById('eventForm');
  if (!form) return;

  form.addEventListener('submit', (e) => {
    let isValid = true;

    ADD_FIELD_MAP.forEach(({ name, errDivId, inputId }) => {
      const el = document.getElementById(inputId);
      if (!el) return;
      // Ajout : la date future est requise (allowPast = false)
      if (!validateField(name, el.value.trim(), errDivId, inputId, false)) {
        isValid = false;
      }
    });

    if (!isValid) {
      e.preventDefault();
      showToast('Veuillez corriger les erreurs avant de continuer.', 'error');
      // Scroll vers la première erreur visible
      const firstInvalid = form.querySelector('.is-invalid');
      if (firstInvalid) firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
  });
});

// ─── Réinitialisation du formulaire ─────────────────────────────────────────
function resetForm() {
  const form = document.getElementById('eventForm');
  if (form) form.reset();

  // Effacer toutes les erreurs
  ADD_FIELD_MAP.forEach(({ errDivId, inputId }) => clearError(errDivId, inputId));

  // Vider le hint de statut
  const hint = document.getElementById('statut-hint');
  if (hint) hint.textContent = '';

  showToast('Formulaire réinitialisé.', 'warning');
}
