document.addEventListener("DOMContentLoaded", function () {
    console.log("TEST CLICK SCRIPT READY");

    console.log("BTN COUNT:", document.querySelectorAll(".toggle-btn").length);


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

    const modal = document.getElementById("modalViewChapitre");
    const content = document.getElementById("viewContent");

    fetch("viewchapitre.php?id=" + id)
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

    // ================= EDIT CHAPITRE =================
window.openView = function (id) {

    const modal = document.getElementById("modalViewChapitre");
    const content = document.getElementById("viewContent");

    if (!modal || !content) {
        console.error("❌ Modal ou content introuvable");
        return;
    }

    fetch("viewchapitre.php?id=" + id)
        .then(res => res.text())
        .then(data => {

            content.innerHTML = data;

            const bsModal = bootstrap.Modal.getOrCreateInstance(modal);
            bsModal.show();

        })
        .catch(err => console.error("❌ VIEW ERROR:", err));
};

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

        fetch(`searchChapitre.php?search=${value}&page=${page}&sort=${sort}`)
            .then(res => res.text())
            .then(data => {
                tableBody.innerHTML = data;
            })
            .catch(err => {
                console.error("❌ AJAX ERROR:", err);
            });
    }

    // ================= SEARCH =================
    if (searchInput) {
        searchInput.addEventListener("keyup", function () {
            loadPage(1);
        });
    }

    // ================= GLOBAL =================
    window.loadPage = loadPage;

    // ================= AUTO LOAD =================
    loadPage(1);
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
});