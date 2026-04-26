/**
 * list_validation.js
 * Logique de la page liste_evenements.php :
 *   - Modal d'édition (ouverture + pré-remplissage + validation + soumission)
 *   - Modal de suppression (confirmation)
 *   - Modal de vue rapide
 *
 * Dépendances (dans l'ordre de chargement) :
 *   1. common.js
 *   2. validate_rules.js
 *   3. ce fichier
 */

'use strict';

// ─── Cartographie champ → ids (modal édition) ────────────────────────────────
// Les ids de champs du modal commencent par "edit-" et les erreurs par "eerr-"
const EDIT_FIELD_MAP = [
  { name: 'titre',     errDivId: 'eerr-titre',  inputId: 'edit-titre'  },
  { name: 'type',      errDivId: 'eerr-type',   inputId: 'edit-type'   },
  { name: 'dateEvent', errDivId: 'eerr-date',   inputId: 'edit-date'   },
  { name: 'duree',     errDivId: 'eerr-duree',  inputId: 'edit-duree'  },
  { name: 'lieu_lien', errDivId: 'eerr-lieu',   inputId: 'edit-lieu'   },
  { name: 'nbplaces',  errDivId: 'eerr-places', inputId: 'edit-places' },
  { name: 'statut',    errDivId: 'eerr-statut', inputId: 'edit-statut' },
];

// ─── Helpers internes ────────────────────────────────────────────────────────

/** Retourne l'instance Bootstrap du modal par son id. */
function _getModal(id) {
  return bootstrap.Modal.getOrCreateInstance(document.getElementById(id));
}

/** Efface toutes les erreurs du modal d'édition. */
function _clearAllEditErrors() {
  EDIT_FIELD_MAP.forEach(({ errDivId, inputId }) => clearError(errDivId, inputId));
}

// ─── Modal d'édition ─────────────────────────────────────────────────────────

/**
 * Ouvre le modal d'édition et pré-remplit les champs avec les données de l'événement.
 * @param {Object} ev  Objet événement (toutes les colonnes de la table)
 */
function openEditModal(ev) {
  _clearAllEditErrors();

  // Pré-remplissage
  document.getElementById('edit-id').value     = ev.ID;
  document.getElementById('edit-titre').value  = ev.Titre;
  document.getElementById('edit-type').value   = ev.Type;
  document.getElementById('edit-desc').value   = ev.Description ?? '';
  document.getElementById('edit-date').value   = ev.dateEvent;   // format Y-m-d
  document.getElementById('edit-duree').value  = ev.duree;
  document.getElementById('edit-lieu').value   = ev.lieu_lien;
  document.getElementById('edit-places').value = ev.nbplaces;
  document.getElementById('edit-statut').value = ev.Statut;

  _getModal('editModal').show();
}

/**
 * Valide le formulaire d'édition et le soumet si tout est correct.
 * Appelé par le bouton "Enregistrer" du modal.
 */
function submitEditForm() {
  let isValid = true;

  EDIT_FIELD_MAP.forEach(({ name, errDivId, inputId }) => {
    const el = document.getElementById(inputId);
    if (!el) return;
    // En mode édition on accepte les dates passées (allowPast = true)
    if (!validateField(name, el.value.trim(), errDivId, inputId, true)) {
      isValid = false;
    }
  });

  if (!isValid) {
    showToast('Veuillez corriger les erreurs dans le formulaire.', 'error');
    // Scroll vers la première erreur du modal
    const firstInvalid = document.getElementById('editForm').querySelector('.is-invalid');
    if (firstInvalid) firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
    return;
  }

  document.getElementById('editForm').submit();
}

// ─── Validation blur dans le modal d'édition ────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  EDIT_FIELD_MAP.forEach(({ name, errDivId, inputId }) => {
    const el = document.getElementById(inputId);
    if (!el) return;

    el.addEventListener('blur', () => {
      // allowPast = true en mode édition
      validateField(name, el.value.trim(), errDivId, inputId, true);
    });

    el.addEventListener('input', () => {
      clearError(errDivId, inputId);
    });
  });

  // ── Ré-ouverture du modal si erreurs PHP (modifier_evenement.php) ─────────
  if (typeof editEventFromPHP !== 'undefined' && editEventFromPHP) {
    openEditModal(editEventFromPHP);
  }
});

// ─── Modal de suppression ─────────────────────────────────────────────────────

/**
 * Ouvre le modal de confirmation de suppression.
 * @param {number} id    ID de l'événement
 * @param {string} name  Titre de l'événement (pour l'affichage)
 */
function openDeleteModal(id, name) {
  document.getElementById('deleteEventName').textContent = name;
  document.getElementById('deleteConfirmBtn').href =
    `../../controller/evenement/supprimer_evenement.php?id=${id}`;
  _getModal('deleteModal').show();
}

// ─── Modal vue rapide ─────────────────────────────────────────────────────────

/**
 * Ouvre le modal de vue rapide d'un événement.
 * Utilise la variable JS `allEvents` injectée par PHP dans la vue.
 * @param {number} id  ID de l'événement
 */
function viewEvent(id) {
  if (typeof allEvents === 'undefined' || !allEvents[id]) return;
  const ev = allEvents[id];

  const typeLabels = {
    workshop:   'Workshop',
    conference: 'Conférence',
    seminaire:  'Séminaire',
    hackathon:  'Hackathon',
    formation:  'Formation',
    webinar:    'Webinaire',
    autre:      'Autre',
  };
  const statutColors = { ouvert: '#71dd37', ferme: '#ff3e1d', complet: '#ffab00' };
  const typeLabel    = typeLabels[ev.Type] ?? ev.Type;
  const statutColor  = statutColors[ev.Statut] ?? '#a1acb8';

  document.getElementById('viewModalTitle').textContent = ev.Titre;
  document.getElementById('viewModalBody').innerHTML = `
    <dl class="row mb-0" style="font-size:.9rem">
      <dt class="col-sm-4">Type</dt>
      <dd class="col-sm-8">${typeLabel}</dd>

      <dt class="col-sm-4">Date</dt>
      <dd class="col-sm-8">${ev.dateEvent}</dd>

      <dt class="col-sm-4">Durée</dt>
      <dd class="col-sm-8">${ev.duree > 0 ? ev.duree + ' heure(s)' : '—'}</dd>

      <dt class="col-sm-4">Lieu / Lien</dt>
      <dd class="col-sm-8" style="word-break:break-all">${ev.lieu_lien}</dd>

      <dt class="col-sm-4">Places</dt>
      <dd class="col-sm-8">${ev.nbplaces}</dd>

      <dt class="col-sm-4">Statut</dt>
      <dd class="col-sm-8">
        <span style="background:${statutColor};color:#fff;padding:.2rem .6rem;
                     border-radius:999px;font-size:.75rem;font-weight:700">
          ${ev.Statut.toUpperCase()}
        </span>
      </dd>

      ${ev.Description ? `
      <dt class="col-sm-4 mt-2">Description</dt>
      <dd class="col-sm-8 mt-2">${ev.Description}</dd>` : ''}
    </dl>`;

  _getModal('viewModal').show();
}
