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

  /* ================= DATA ================= */
  let blocks = [];

  function uid() {
    return "id_" + Date.now() + "_" + Math.random().toString(16).slice(2);
  }

  /* ================= YOUTUBE ================= */
  function getYoutubeEmbed(url) {
    const regExp = /(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&]+)/;
    const match = url.match(regExp);
    return match ? `https://www.youtube.com/embed/${match[1]}` : null;
  }

  /* ================= PASTE ================= */
  document.addEventListener("paste", function (e) {
    const text = e.clipboardData.getData("text");

    if (!text) return;

    const yt = getYoutubeEmbed(text);

    if (yt) {
      blocks.push({
        id: uid(),
        type: "youtube",
        content: yt
      });

      render();
    }
  });

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

  /* ================= FILE UPLOAD ================= */
  dropZone.addEventListener("click", () => fileInput.click());

  fileInput.addEventListener("change", (e) => {
    handleFiles(e.target.files);
    fileInput.value = "";
  });

  dropZone.addEventListener("dragover", e => e.preventDefault());

  dropZone.addEventListener("drop", e => {
    e.preventDefault();

    const text =
      e.dataTransfer.getData("text/uri-list") ||
      e.dataTransfer.getData("text/plain");

    if (text) {
      const yt = getYoutubeEmbed(text);

      if (yt) {
        blocks.push({
          id: uid(),
          type: "youtube",
          content: yt
        });

        render();
        return;
      }
    }

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
        type,
        file,
        content: URL.createObjectURL(file)
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

      /* YOUTUBE */
      else if (b.type === "youtube") {
        const iframe = document.createElement("iframe");
        iframe.src = b.content;
        iframe.width = "100%";
        iframe.height = "315";
        iframe.allowFullscreen = true;
        iframe.allow =
          "accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture";

        div.appendChild(iframe);
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

  /* ================= UPLOAD FILE ================= */
  async function uploadFile(file) {
    const fd = new FormData();
    fd.append("file", file);

    const res = await fetch("upload.php", {
      method: "POST",
      body: fd
    });

    return await res.text();
  }

  /* ================= SUBMIT ================= */
  form.addEventListener("submit", async function (e) {
    e.preventDefault();

    const chapitre_id = document.querySelector('select[name="chapitre_id"]').value;

    if (!chapitre_id) return alert("Choisis un chapitre !");
    if (blocks.length === 0) return alert("Ajoute du contenu !");

    const fd = new FormData();
    fd.append("chapitre_id", chapitre_id);

    for (let i = 0; i < blocks.length; i++) {

      let b = blocks[i];

      fd.append(`blocks[${i}][type]`, b.type);
      fd.append(`blocks[${i}][ordre]`, i + 1);

      if (b.type === "text") {
        fd.append(`blocks[${i}][contenu]`, b.content);
      }

      if (b.type === "youtube") {
        fd.append(`blocks[${i}][contenu]`, b.content);
      }

      if (b.file) {
        const url = await uploadFile(b.file);
        fd.append(`blocks[${i}][contenu]`, url);
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
    .catch(console.error);
  });

});