document.addEventListener("DOMContentLoaded", function () {

  /* ================= QUILL ================= */
  const quill = new Quill('#editor', {
    theme: 'snow',
    modules: { toolbar: '#toolbar' }
  });

  /* ================= ELEMENTS ================= */
  const form = document.getElementById("formContenu");
  const addTextBtn = document.getElementById("addText");
  const dropZone = document.getElementById("drop-zone");
  const fileInput = document.getElementById("fileInput");
  const preview = document.getElementById("preview");

  /* ================= DATA STRUCTURE ================= */
  let blocks = [];

  function uid() {
    return "id_" + Date.now() + "_" + Math.random().toString(16).slice(2);
  }

  /* ================= ADD TEXT ================= */
  addTextBtn.addEventListener("click", function (e) {
    e.preventDefault();

    const html = quill.root.innerHTML;

    if (!html || html === "<p><br></p>") return;

    blocks.push({
      id: uid(),
      type: "text",
      content: html
    });

    quill.setText("");
    render();
  });

  /* ================= FILE UPLOAD (local preview) ================= */
  dropZone.addEventListener("click", () => fileInput.click());

  fileInput.addEventListener("change", (e) => {
    handleFiles(e.target.files);
    fileInput.value = "";
  });

  dropZone.addEventListener("dragover", e => e.preventDefault());

  dropZone.addEventListener("drop", e => {
    e.preventDefault();
    handleFiles(e.dataTransfer.files);
  });

  function handleFiles(files) {
    for (let file of files) {

      let type = null;

      if (file.type.startsWith("image/")) type = "image";
      else if (file.type.startsWith("video/")) type = "video";
      else if (file.type === "application/pdf") type = "pdf";
      else continue;

      blocks.push({
        id: uid(),
        type: type,
        file: file,
        content: URL.createObjectURL(file) // preview local
      });
    }

    render();
  }

  /* ================= RENDER ================= */
  function render() {
    preview.innerHTML = "";

    blocks.forEach((b) => {

      const div = document.createElement("div");
      div.className = "item border p-2 mb-2 position-relative";
      div.dataset.id = b.id;

      /* DELETE */
      const del = document.createElement("button");
      del.innerHTML = "✖";
      del.className = "btn btn-sm btn-danger";
      del.style.position = "absolute";
      del.style.top = "5px";
      del.style.right = "5px";

      del.onclick = () => {
        blocks = blocks.filter(x => x.id !== b.id);
        render();
      };

      div.appendChild(del);

      /* TEXT */
      if (b.type === "text") {
        const d = document.createElement("div");
        d.innerHTML = b.content;
        div.appendChild(d);
      }

      /* IMAGE */
      else if (b.type === "image") {
        const img = document.createElement("img");
        img.src = b.content;
        img.style.width = "100%";
        div.appendChild(img);
      }

      /* VIDEO */
      else if (b.type === "video") {
        const v = document.createElement("video");
        v.src = b.content;
        v.controls = true;
        v.style.width = "100%";
        div.appendChild(v);
      }

      /* PDF */
      else if (b.type === "pdf") {
        const btn = document.createElement("button");
        btn.className = "btn btn-primary btn-sm";
        btn.textContent = "Ouvrir PDF";

        btn.onclick = () => window.open(b.content, "_blank");

        div.appendChild(btn);
      }

      preview.appendChild(div);
    });
  }

  /* ================= DRAG SORT ================= */
  Sortable.create(preview, {
    animation: 150,
    onEnd: () => {

      let newBlocks = [];

      document.querySelectorAll("#preview .item").forEach(el => {
        const b = blocks.find(x => x.id === el.dataset.id);
        if (b) newBlocks.push(b);
      });

      blocks = newBlocks;
    }
  });

  /* ================= UPLOAD FUNCTION (IMPORTANT) ================= */
  async function uploadFile(file) {
    const fd = new FormData();
    fd.append("file", file);

    const res = await fetch("upload.php", {
      method: "POST",
      body: fd
    });

    const path = await res.text();

    return path; // ex: uploads/abc123.jpg
  }

  /* ================= SUBMIT ================= */
  form.addEventListener("submit", async function (e) {
    e.preventDefault();

    const chapitre_id = document.querySelector('select[name="chapitre_id"]').value;

    if (!chapitre_id) {
      alert("Choisis un chapitre !");
      return;
    }

    if (blocks.length === 0) {
      alert("Ajoute du contenu !");
      return;
    }

    const fd = new FormData();
    fd.append("chapitre_id", chapitre_id);

    for (let i = 0; i < blocks.length; i++) {

      let b = blocks[i];

      fd.append(`blocks[${i}][type]`, b.type);
      fd.append(`blocks[${i}][ordre]`, i + 1);

      /* TEXT */
      if (b.type === "text") {
        fd.append(`blocks[${i}][contenu]`, b.content);
      }

      /* FILE → upload JS */
      if (b.file) {
        const url = await uploadFile(b.file);
        fd.append(`blocks[${i}][contenu]`, url); // uploads/file.jpg
      }
    }

    fetch("save_contenu.php", {
      method: "POST",
      body: fd
    })
    .then(res => res.text())
    .then(data => {
      console.log("SERVER:", data);

      alert("Contenu enregistré !");

      blocks = [];
      render();
      quill.setText("");
    })
    .catch(err => console.error(err));
  });

});