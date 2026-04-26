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
    window.openView = function (id) {

    const modal = document.getElementById("modalViewChapitre");
    const content = document.getElementById("viewContent");

    fetch("viewchapitre.php?id=" + id)
        .then(res => res.text())
        .then(data => {

            if (content) content.innerHTML = data;

            const bsModal = new bootstrap.Modal(modal);
            bsModal.show(); // ✅ CORRECT

        })
        .catch(err => console.error("❌ VIEW ERROR:", err));
};
    // ================= EDIT =================
    window.openEdit = function (f) {

        const set = (id, value) => {
            const el = document.getElementById(id);
            if (el) el.value = value;
        };

        set("edit_id", f.id_f);
        set("edit_titre", f.titre);
        set("edit_desc", f.description);
        set("edit_owner", f.nom_propr);
        set("edit_etat", f.etat);

        const oldImage = document.getElementById("oldImage");
        const preview = document.getElementById("previewImg");

        if (oldImage) {
            oldImage.src = "/skiller5/" + f.image;
            oldImage.style.display = "block";
        }

        if (preview) {
            preview.style.display = "none";
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

            if (oldImage) {
                oldImage.style.display = "none";
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

});