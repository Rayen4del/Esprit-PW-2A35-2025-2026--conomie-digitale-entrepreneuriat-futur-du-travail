document.addEventListener("DOMContentLoaded", function () {
  document.querySelector('select[name="id_f"]').addEventListener('change', function () {
      let id_f = this.value;

      if (!id_f) return;

    fetch('./GetLastorder.php?id_f=' + encodeURIComponent(id_f))
      .then(res => res.text())
      .then(data => {
        const v = parseInt(data);
        document.getElementById('ordre').value = Number.isFinite(v) ? v : 1;
      })
      .catch(err => {
        console.error("Erreur fetch ordre:", err);
        document.getElementById('ordre').value = 1;
      });
  });
  // initialize ordre when page loads and a formation is preselected
  const select = document.querySelector('select[name="id_f"]');
  if (select && select.value) {
    select.dispatchEvent(new Event('change'));
  }

  const form = document.getElementById("chapitreForm");

  const titre = document.getElementById("titre_c");
  const formation = document.getElementById("id_f");
  const ordre = document.getElementById("ordre");

  form.addEventListener("submit", function (e) {

    let valid = true;

    // RESET ERRORS
    document.getElementById("errTitre").innerText = "";
    document.getElementById("errFormation").innerText = "";
    document.getElementById("errOrdre").innerText = "";

    // ================= TITRE =================
    if (!titre.value.trim()) {
      document.getElementById("errTitre").innerText = "Le titre est obligatoire";
      valid = false;
    }
    else if (titre.value.trim().length < 3) {
      document.getElementById("errTitre").innerText = "Minimum 3 caractères";
      valid = false;
    }

    // ================= FORMATION =================
    if (!formation.value) {
      document.getElementById("errFormation").innerText = "Veuillez sélectionner une formation";
      valid = false;
    }

    // ================= ORDRE =================
    if (!ordre.value || ordre.value <= 0) {
      document.getElementById("errOrdre").innerText = "Ordre invalide";
      valid = false;
    }

    // STOP SUBMIT
    if (!valid) {
      e.preventDefault();
    }
  });

});