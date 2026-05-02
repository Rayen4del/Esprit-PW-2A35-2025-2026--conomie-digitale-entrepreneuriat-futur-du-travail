document.addEventListener("DOMContentLoaded", function () {

    console.log("✔ consultertests.js chargé");

    // ================= ELEMENTS =================
    const searchInput = document.getElementById("searchInput");
    const tableBody = document.getElementById("tableBody");

    // ================= TOAST =================
    window.showDangerToast = function (message) {
        const toastEl = document.getElementById("dangerToast");

        if (!toastEl) return;

        const body = toastEl.querySelector(".toast-body");
        if (body) body.innerText = message;

        const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
        toast.show();
    };

    // ================= VIEW TEST =================
    window.openView = function (id_t) {

        const modal = document.getElementById("modalViewTest");
        const content = document.getElementById("viewContent");

        if (!modal || !content) return;

        fetch("viewtest.php?id=" + id_t)
            .then(res => res.text())
            .then(data => {

                content.innerHTML = data;

                const bsModal = bootstrap.Modal.getOrCreateInstance(modal);
                bsModal.show();

            })
            .catch(err => console.error("❌ VIEW TEST ERROR:", err));
    };

    // ================= EDIT TEST =================
    window.openEdit = function (test) {

        document.getElementById("edit_id").value = test.id_t;
        document.getElementById("edit_idc").value = test.id_c;
        document.getElementById("edit_idf").value = test.id_f;
        document.getElementById("edit_score").value = test.score_min;
        document.getElementById("edit_date").value = test.date_creation;

        const modal = document.getElementById("modalEditTest");
        const bsModal = bootstrap.Modal.getOrCreateInstance(modal);
        bsModal.show();
    };

    // ================= PAGINATION + SEARCH =================
    let currentPage = 1;

    function loadPage(page = 1) {

        if (!tableBody) return;

        currentPage = page;

        const value = searchInput ? searchInput.value : "";
        const sort = document.body.dataset.sort || "ASC";

        fetch(`searchTest.php?search=${value}&page=${page}&sort=${sort}`)
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

});