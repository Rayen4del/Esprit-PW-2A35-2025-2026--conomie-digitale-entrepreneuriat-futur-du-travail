document.addEventListener("DOMContentLoaded", function () {

    console.log("✔ consulterformation.js chargé");

    // ================= ELEMENTS =================
    const searchInput = document.getElementById("searchInput");
    const tableBody = document.getElementById("tableBody");

    // ================= TOAST =================
    window.showDangerToast = function (message) {
        const toastEl = document.getElementById("dangerToast");

        if (!toastEl) return;

        const body = toastEl.querySelector(".toast-body");
        if (body) body.innerText = message;

        if (typeof bootstrap !== "undefined") {
            const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
            toast.show();
        }
    };

    // ================= VIEW =================
 let viewModalInstance = null;

window.openView = function (id) {

    const modal = document.getElementById("modalViewFormation");
    const content = document.getElementById("viewchpitre");

    const btnChapitre = document.getElementById("btnChapitre");
    const btnTest = document.getElementById("btnTest");

    btnChapitre.href = "../Chapitre/ajouterchapitre.php?id=" + id;
    btnTest.href = "../Test/ajoutertest.php?id=" + id;

    fetch("viewformation.php?id=" + id + "&ajax=1")
        .then(res => res.text())
        .then(data => {

            if (content) content.innerHTML = data;

            if (!viewModalInstance) {
                viewModalInstance = new bootstrap.Modal(modal);
            }

            viewModalInstance.show();
        })
        .catch(err => console.error("❌ VIEW ERROR:", err));
};
const modalEl = document.getElementById("modalViewFormation");

modalEl.addEventListener("hidden.bs.modal", function () {
    document.body.classList.remove("modal-open");

    document.querySelectorAll(".modal-backdrop").forEach(el => el.remove());
});
    // ================= EDIT =================
    window.openEdit = function (f) {

    const set = (id, value) => {
        const el = document.getElementById(id);
        if (el) el.value = value;
    };
    
    // ================= FORM VALUES =================
    set("edit_id", f.id_f);
    set("edit_titre", f.titre);
    set("edit_desc", f.description);
    set("edit_owner", f.nom_propr);
    set("edit_etat", f.etat);

    // ================= IMAGES =================
    const oldImage = document.getElementById("oldImage");
    const previewImg = document.getElementById("previewImg");
    console.log("IMAGE FROM DB:", f.image);
    console.log("UPLOAD_URL:", UPLOAD_URL);
    if (oldImage) {
        oldImage.src = UPLOAD_URL + f.image;
        oldImage.style.display = "block";
    }

    if (previewImg) {
        previewImg.style.display = "none";
        previewImg.src = "";
    }
};  

    // ================= IMAGE UPLOAD =================
    const dropZone = document.getElementById("dropZone");
    const fileInput = document.getElementById("edit_image");
    const oldImage = document.getElementById("oldImage");
    const preview = document.getElementById("previewImg");

    function showPreview(file) {
        if (!file) return;

        const reader = new FileReader();

        reader.onload = function (e) {
            if (preview) {
                preview.src = e.target.result;
                preview.style.display = "block";
            }
        };

        reader.readAsDataURL(file);
    }

    if (dropZone && fileInput) {

        dropZone.addEventListener("click", () => fileInput.click());

        fileInput.addEventListener("change", function () {
            showPreview(this.files[0]);
        });

        dropZone.addEventListener("dragover", (e) => {
            e.preventDefault();
        });

        dropZone.addEventListener("drop", (e) => {
            e.preventDefault();
            fileInput.files = e.dataTransfer.files;
            showPreview(e.dataTransfer.files[0]);
        });
    }

    // ================= PAGINATION + SEARCH =================
    let currentPage = 1;

    function loadPage(page = 1) {

        if (!tableBody) {
            console.error("❌ tableBody introuvable");
            return;
        }

        currentPage = page;

        const value = searchInput ? searchInput.value : "";
        const sort = document.body.dataset.sort || "ASC";

        fetch(`searchFormation.php?search=${value}&page=${page}&sort=${sort}`)
            .then(res => res.text())
            .then(data => {
                tableBody.innerHTML = data;
            })
            .catch(err => {
                console.error("❌ Erreur AJAX:", err);
            });
    }

    // SEARCH EVENT
    if (searchInput) {
        searchInput.addEventListener("keyup", function () {
            loadPage(1);
        });
    }

    // EXPOSE GLOBAL
    window.loadPage = loadPage;

    // AUTO LOAD
    loadPage(1);
    // ================= VALIDATION FORM EDIT =================


    const form = document.querySelector("#modalEditFormation form");

    if (!form) return;

    form.addEventListener("submit", function (e) {

    let valid = true;

    const titre = edit_titre.value.trim();
    const description = edit_desc.value.trim();
    const nom = edit_owner.value.trim();
    const etat = edit_etat.value;
    const imageInput = edit_image;

    const hasNewImage = imageInput.files.length > 0;
    const hasOldImage = oldImage && oldImage.style.display !== "none" && oldImage.src;

    const setErr = (id, msg = "") => {
        const el = document.getElementById(id);
        if (el) el.innerText = msg;
    };

    // reset
    setErr("errTitre");
    setErr("errDescription");
    setErr("errNom");
    setErr("errImage");
    setErr("errEtat");

    // TITRE
    if (titre.length < 3) {
        setErr("errTitre", "Le titre doit contenir au moins 3 caractères");
        valid = false;
    }

    // DESCRIPTION
    if (description.length < 10) {
        setErr("errDescription", "La description doit contenir au moins 10 caractères");
        valid = false;
    }

    // NOM
    if (nom.length < 3) {
        setErr("errNom", "Nom invalide");
        valid = false;
    }

    // IMAGE
    if (!hasNewImage && !hasOldImage) {
        setErr("errImage", "Veuillez choisir une image");
        valid = false;
    }

    // ETAT
    if (!etat) {
        setErr("errEtat", "Veuillez sélectionner un état");
        valid = false;
    }

    if (!valid) e.preventDefault();
});

});
  