document.addEventListener("DOMContentLoaded", function () {


    let currentChapitreId = null;
    console.log("✔ consulterchapitres.js chargé");

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

    // ================= VIEW CHAPITRE =================
    window.openView = function (id) {

    currentChapitreId = id; // 🔥 sauvegarde ID

    const modal = document.getElementById("modalViewChapitre");
    const content = document.getElementById("viewContent");
    const btncn= document.getElementById("btnajcont");
    btncn.href = "ajoutercontenu.php?id=" + id;
    fetch("viewchapitre.php?id=" + id)
        .then(res => res.text())
        .then(data => {

            content.innerHTML = data;

            const bsModal = bootstrap.Modal.getOrCreateInstance(modal);
            bsModal.show();
        });
};
    console.log("JS chargé ✅");

  const params = new URLSearchParams(window.location.search);
  const id = params.get("open");

  console.log("ID récupéré =", id);

  if (id) {
    console.log("Appel openView avec ID =", id);
    openView(id)
  }
    // ================= EDIT CHAPITRE =================
window.openEdit = function (data) {

    console.log("EDIT RAW =", data);

    // si string → convertir en objet
    if (typeof data === "string") {
        try {
            data = JSON.parse(data);
        } catch (e) {
            console.error("JSON invalide", data);
            return;
        }
    }

    if (!data) return;

    document.getElementById("edit_id").value = data.id_c || "";
    document.getElementById("edit_titre").value = data.titre_c || "";
    document.getElementById("edit_ordre").value = data.ordre || "";
    document.getElementById("edit_idf").value = data.id_f || "";

    const modalEl = document.getElementById("modalEditChapitre");
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
};

    // ================= LOAD PAGE (AJAX) =================
    function loadPage(page = 1) {

        const value = searchInput ? searchInput.value : "";
        const sort = document.body.dataset.sort || "ASC";

        // ✅ IMPORTANT : filtre formation depuis URL
        const id_f = new URLSearchParams(window.location.search).get("id_f");

        fetch(`searchchapitres.php?search=${value}&page=${page}&sort=${sort}&id_f=${id_f ?? ""}`)
            .then(res => res.text())
            .then(data => {
                if (tableBody) {
                    tableBody.innerHTML = data;
                }
            })
            .catch(err => console.error("AJAX ERROR:", err));
    }

    // ================= SEARCH =================
    if (searchInput) {
        searchInput.addEventListener("keyup", function () {
            loadPage(1);
        });
    }

    // ================= PAGINATION GLOBAL =================
    window.loadPage = loadPage;

    // ================= AUTO LOAD =================
    loadPage(1);

    // ================= TOGGLE (OPTIONNEL UI) =================
    document.addEventListener("click", function (e) {

        const btn = e.target.closest(".toggle-btn");
        if (!btn) return;

        const card = btn.closest(".content-card");
        const body = card.querySelector(".content-body");

        if (!body) return;

        if (body.style.display === "block") {
            body.style.display = "none";
            btn.innerHTML = "▼";
        } else {
            body.style.display = "block";
            btn.innerHTML = "▲";
        }
    });

    // ================= UPDATE URL WHEN FILTER CHANGES =================
    const selectFormation = document.querySelector("select[name='id_f']");

    if (selectFormation) {
        selectFormation.addEventListener("change", function () {

            const url = new URL(window.location);
            url.searchParams.set("id_f", this.value);
            window.history.pushState({}, "", url);

            loadPage(1);
        });
    }
function updateOrder() {
    const items = document.querySelectorAll(".content-item");
    const order = [];

    items.forEach((el, index) => {
        order.push({
            id: el.dataset.id,
            ordre: index + 1
        });
    });

    fetch("update_order.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify(order)
    });
}

// 🔼 MOVE UP
document.addEventListener("click", function(e) {
    if (e.target.classList.contains("move-up")) {
        const item = e.target.closest(".content-item");
        const prev = item.previousElementSibling;

        if (prev) {
            item.parentNode.insertBefore(item, prev);
            updateOrder();
        }
    }
});

// 🔽 MOVE DOWN
document.addEventListener("click", function(e) {
    if (e.target.classList.contains("move-down")) {
        const item = e.target.closest(".content-item");
        const next = item.nextElementSibling;

        if (next) {
            item.parentNode.insertBefore(next, item);
            updateOrder();
        }
    }
});
window.openAddForm = function (idChapitre) {

    const container = document.querySelector(".paper");

    fetch("ajoutercontenu.php?id=" + idChapitre)
        .then(res => res.text())
        .then(html => {

            // remplacer tout le contenu du paper
            container.innerHTML = html;

        })
        .catch(err => console.error("ADD FORM ERROR:", err));
};
window.goBackView = function () {

    const content = document.getElementById("viewContent");

    if (!currentChapitreId) return;

    fetch("viewchapitre.php?id=" + currentChapitreId)
        .then(res => res.text())
        .then(html => {
            content.innerHTML = html;
        });
};


//_============================================================================================================================//
 

});