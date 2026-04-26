/**
 * validate_rules.js
 * Règles de validation et fonctions utilitaires partagées.
 * Doit être chargé AVANT form_validation.js et list_validation.js.
 */

'use strict';

// ─── Règles de validation ───────────────────────────────────────────────────
const EVENT_RULES = {
  titre: {
    label:    'Le titre',
    minLen:   3,
    maxLen:   200,
    required: true,
  },
  type: {
    label:    'Le type',
    required: true,
    allowed:  ['workshop', 'conference', 'seminaire', 'hackathon', 'formation', 'webinar', 'autre'],
  },
  dateEvent: {
    label:    "La date de l'événement",
    required: true,
    future:   true,   // false = on accepte les dates passées (mode édition)
  },
  duree: {
    label:    'La durée',
    required: false,
    minNum:   0,
    maxNum:   720,
    numeric:  true,
  },
  lieu_lien: {
    label:    'Le lieu / lien',
    required: true,
    minLen:   2,
    maxLen:   255,
  },
  nbplaces: {
    label:    'Le nombre de places',
    required: true,
    minNum:   1,
    maxNum:   99999,
    numeric:  true,
  },
  statut: {
    label:    'Le statut',
    required: true,
    allowed:  ['ouvert', 'ferme', 'complet'],
  },
};

// ─── Utilitaires DOM ────────────────────────────────────────────────────────

/**
 * Efface le message d'erreur et retire la classe is-invalid du champ.
 * @param {string} errDivId  id de la div contenant le message
 * @param {string} inputId   id du champ input/select
 */
function clearError(errDivId, inputId) {
  const errEl = document.getElementById(errDivId);
  if (errEl) errEl.textContent = '';
  const inputEl = document.getElementById(inputId);
  if (inputEl) inputEl.classList.remove('is-invalid');
}

/**
 * Affiche un message d'erreur et ajoute la classe is-invalid.
 * @param {string} errDivId  id de la div contenant le message
 * @param {string} inputId   id du champ input/select
 * @param {string} msg       message d'erreur
 */
function setError(errDivId, inputId, msg) {
  const errEl = document.getElementById(errDivId);
  if (errEl) errEl.textContent = msg;
  const inputEl = document.getElementById(inputId);
  if (inputEl) inputEl.classList.add('is-invalid');
}

// ─── Moteur de validation ───────────────────────────────────────────────────

/**
 * Valide un champ selon les règles EVENT_RULES.
 *
 * @param {string}  fieldName   Clé dans EVENT_RULES (ex: 'titre', 'dateEvent')
 * @param {string}  value       Valeur (déjà trimée)
 * @param {string}  errDivId    id de la div d'erreur (ex: 'err-titre')
 * @param {string}  inputId     id du champ (ex: 'event-titre')
 * @param {boolean} allowPast   true = accepter les dates passées (mode édition)
 * @returns {boolean}
 */
function validateField(fieldName, value, errDivId, inputId, allowPast = false) {
  clearError(errDivId, inputId);

  const r = EVENT_RULES[fieldName];
  if (!r) return true; // règle inconnue → on ne bloque pas

  // ── Champ obligatoire ────────────────────────────────────────────────────
  if (r.required && value === '') {
    setError(errDivId, inputId, `${r.label} est obligatoire.`);
    return false;
  }

  // ── Valeurs autorisées (enum) ────────────────────────────────────────────
  if (r.allowed && value !== '' && !r.allowed.includes(value)) {
    setError(errDivId, inputId, `${r.label} : valeur invalide.`);
    return false;
  }

  // ── Longueur (champs texte) ──────────────────────────────────────────────
  if (!r.numeric && value !== '') {
    if (r.minLen !== undefined && value.length < r.minLen) {
      setError(errDivId, inputId,
        `${r.label} doit contenir au moins ${r.minLen} caractère(s).`);
      return false;
    }
    if (r.maxLen !== undefined && value.length > r.maxLen) {
      setError(errDivId, inputId,
        `${r.label} ne peut pas dépasser ${r.maxLen} caractères.`);
      return false;
    }
  }

  // ── Numérique ────────────────────────────────────────────────────────────
  if (r.numeric && value !== '') {
    const n = Number(value);
    if (isNaN(n)) {
      setError(errDivId, inputId, `${r.label} doit être un nombre.`);
      return false;
    }
    if (r.minNum !== undefined && n < r.minNum) {
      setError(errDivId, inputId, `${r.label} doit être au moins ${r.minNum}.`);
      return false;
    }
    if (r.maxNum !== undefined && n > r.maxNum) {
      setError(errDivId, inputId, `${r.label} semble invalide (max ${r.maxNum}).`);
      return false;
    }
  }

  // ── Date ─────────────────────────────────────────────────────────────────
  if (fieldName === 'dateEvent' && value !== '') {
    const chosen = new Date(value);
    if (isNaN(chosen.getTime())) {
      setError(errDivId, inputId, 'Format de date invalide.');
      return false;
    }
    if (r.future && !allowPast) {
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      if (chosen < today) {
        setError(errDivId, inputId, 'La date ne peut pas être dans le passé.');
        return false;
      }
    }
  }

  return true;
}
