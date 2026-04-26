function validateRegistrationForm() {
  let isValid = true;

  // Clear previous errors
  document.querySelectorAll('.field-error').forEach(el => el.textContent = '');

  const idUser = document.getElementById('idUtilisateur').value.trim();
  if (!idUser || isNaN(idUser) || parseInt(idUser) <= 0) {
    document.getElementById('err-idUtilisateur').textContent = "ID Utilisateur est obligatoire.";
    isValid = false;
  }

  const statut = document.getElementById('statut').value;
  if (!statut) {
    document.getElementById('err-statut').textContent = "Veuillez sélectionner un statut.";
    isValid = false;
  }

  return isValid;
}