// ===============================
// ajouterformation.js
// ===============================

document.addEventListener("DOMContentLoaded", function () {

    // ================= FORM =================
    const form = document.getElementById("formationForm");

    // ================= ELEMENTS =================
    const dropZone = document.getElementById("dropZone");
    const input = document.getElementById("imageInput");
    const preview = document.getElementById("preview");

    // ================= IMAGE UPLOAD =================
    if (dropZone && input) {

        dropZone.addEventListener("click", () => input.click());

        dropZone.addEventListener("dragover", (e) => {
            e.preventDefault();
            dropZone.style.background = "#eef";
        });

        dropZone.addEventListener("dragleave", () => {
            dropZone.style.background = "transparent";
        });

        dropZone.addEventListener("drop", (e) => {
            e.preventDefault();
            input.files = e.dataTransfer.files;
            showPreview(input.files[0]);
        });

        input.addEventListener("change", () => {
            showPreview(input.files[0]);
        });

        function showPreview(file) {
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function (e) {
                preview.src = e.target.result;
                preview.style.display = "block";
            };
            reader.readAsDataURL(file);
        }
    }

    // ================= VALIDATION FORM =================
    if (form) {

        form.addEventListener("submit", function (e) {

            let valid = true;

            // reset erreurs
            document.querySelectorAll("small.text-danger").forEach(el => {
                el.innerText = "";
            });

            // values
            const titre = document.getElementById("titre").value.trim();
            const description = document.getElementById("description").value.trim();
            const nom = document.getElementById("nomProprietaire").value.trim();
            const image = document.getElementById("imageInput").files.length;
            const etat = document.getElementById("etat").value;

            // ================= TITRE =================
            if (titre.length < 3) {
                document.getElementById("errTitre").innerText =
                    "Le titre doit contenir au moins 3 caractères";
                valid = false;
            }

            // ================= DESCRIPTION =================
            if (description.length < 10) {
                document.getElementById("errDescription").innerText =
                    "La description doit contenir au moins 10 caractères";
                valid = false;
            }

            // ================= NOM =================
            if (nom.length < 3) {
                document.getElementById("errNom").innerText =
                    "Nom invalide";
                valid = false;
            }

            // ================= IMAGE =================
            if (image === 0) {
                document.getElementById("errImage").innerText =
                    "Veuillez choisir une image";
                valid = false;
            }

            // ================= ETAT =================
            if (etat === "") {
                document.getElementById("errEtat").innerText =
                    "Veuillez sélectionner un état";
                valid = false;
            }

            // STOP SUBMIT
            if (!valid) {
                e.preventDefault();
            }
        });
    }

});