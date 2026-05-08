

function validerFormulaire() {
  event.preventDefault(); // Empêche l’envoi du formulaire tant que les contrôles ne sont pas validés

  // Récupération des champs
  let titre = document.getElementById("title").value.trim();
  let auteur = document.getElementById("author").value.trim();
  let datePublication = document.getElementById("publicationDate").value;
  let langue = document.querySelector('input[name="language"]:checked');
  let statut = document.getElementById("status").value;
  let copies = document.getElementById("copies").value;

  // Vérification du titre
  if (titre.length < 3) {
    alert("❌ Le titre doit contenir au moins 3 caractères.");
    return false;
  }

  // Vérification de l'auteur
  let regexAuteur = /^[A-Za-zÀ-ÖØ-öø-ÿ\s]+$/; // Lettres + espaces
  if (!regexAuteur.test(auteur) || auteur.length < 3) {
    alert("❌ L'auteur doit contenir uniquement des lettres (min. 3 caractères).");
    return false;
  }

  // Vérification de la date de publication
  if (!datePublication) {
    alert("❌ La date de publication est obligatoire.");
    return false;
  }
  let dateSaisie = new Date(datePublication);
  let dateActuelle = new Date();
  dateActuelle.setHours(0, 0, 0, 0); // On compare uniquement la date
  if (dateSaisie <= dateActuelle) {
    alert("❌ La date de publication doit être ultérieure à la date du système.");
    return false;
  }

  // Vérification de la langue
  if (!langue || (langue.value !== "AN" && langue.value !== "FR")) {
    alert("❌ La langue doit être AN ou FR.");
    return false;
  }

  // Vérification du statut
  if (statut !== "available" && statut !== "unavailable") {
    alert("❌ Le statut doit être 'Available' ou 'Unavailable'.");
    return false;
  }

  // Vérification du nombre de copies
  if (isNaN(copies) || copies < 1) {
    alert("❌ Le nombre d'exemplaires doit être un entier positif (≥ 1).");
    return false;
  }

  // Si tout est correct
  alert("✅ Livre ajouté avec succès !");
  return true;
}


/*

// Partie 2 - Validation du formulaire Add Book

document.getElementById("addBookForm").addEventListener("submit", function(event) {
    event.preventDefault();

    var title = document.getElementById("title").value.trim();
    var author = document.getElementById("author").value.trim();
    var publicationDate = document.getElementById("publication_date").value;
    var language = document.querySelector('input[name="language"]:checked');
    var status = document.getElementById("status").value;
    var copies = document.getElementById("copies").value;
    var category = document.getElementById("category").value;

    var isValid = true;

    // Fonction pour afficher les messages
    function displayMessage(id, message, isError) {
        var element = document.getElementById(id + "_error");
        element.style.color = isError ? "red" : "green";
        element.innerText = message;
    }

    // Vérification du titre
    if (title.length < 3) {
        displayMessage("title", "Le titre doit contenir au moins 3 caractères.", true);
        isValid = false;
    } else {
        displayMessage("title", "Correct", false);
    }

    // Vérification de l'auteur
    var authorPattern = /^[A-Za-zÀ-ÖØ-öø-ÿ\s]{3,}$/;
    if (!authorPattern.test(author)) {
        displayMessage("author", "L'auteur doit contenir uniquement des lettres et au moins 3 caractères.", true);
        isValid = false;
    } else {
        displayMessage("author", "Correct", false);
    }

    // Vérification date de publication
    if (!publicationDate) {
        displayMessage("publication_date", "La date de publication est obligatoire.", true);
        isValid = false;
    } else {
        var dateSaisie = new Date(publicationDate);
        var dateActuelle = new Date();
        dateActuelle.setHours(0,0,0,0);
        if (dateSaisie <= dateActuelle) {
            displayMessage("publication_date", "La date doit être ultérieure à aujourd'hui.", true);
            isValid = false;
        } else {
            displayMessage("publication_date", "Correct", false);
        }
    }

    // Vérification de la langue
    if (!language || (language.value !== "AN" && language.value !== "FR")) {
        displayMessage("language", "Choisissez AN ou FR.", true);
        isValid = false;
    } else {
        displayMessage("language", "Correct", false);
    }

    // Vérification du statut
    if (status !== "available" && status !== "unavailable") {
        displayMessage("status", "Le statut doit être 'Available' ou 'Unavailable'.", true);
        isValid = false;
    } else {
        displayMessage("status", "Correct", false);
    }

    // Vérification du nombre de copies
    if (isNaN(copies) || copies < 1) {
        displayMessage("copies", "Le nombre d'exemplaires doit être ≥ 1.", true);
        isValid = false;
    } else {
        displayMessage("copies", "Correct", false);
    }

    // Vérification de la catégorie
    if (!category) {
        displayMessage("category", "Veuillez choisir une catégorie.", true);
        isValid = false;
    } else {
        displayMessage("category", "Correct", false);
    }

    // ✅ Si tout est correct
    if (isValid) {
        alert("Livre ajouté avec succès !");
        this.reset();
    }
});*/

/*
//partie 3
const form = document.getElementById("addBookForm");

// Vérification du titre en temps réel (keyup)
document.getElementById("title").addEventListener("keyup", function() {
    const value = this.value.trim();
    const msg = document.getElementById("title_error");

    if (value.length >= 3) {
        msg.style.color = "green";
        msg.innerText = "✅ Titre valide";
    } else {
        msg.style.color = "red";
        msg.innerText = "❌ Le titre doit contenir au moins 3 caractères";
    }
});

// Vérification auteur au blur
document.getElementById("author").addEventListener("blur", function() {
    const value = this.value.trim();
    const msg = document.getElementById("author_error");
    const regex = /^[A-Za-zÀ-ÖØ-öø-ÿ\s]{3,}$/;

    if (regex.test(value)) {
        msg.style.color = "green";
        msg.innerText = "✅ Auteur valide";
    } else {
        msg.style.color = "red";
        msg.innerText = "❌ L'auteur doit contenir au moins 3 lettres et uniquement lettres/espaces";
    }
});



// Date de publication
document.getElementById("publication_date").addEventListener("change", function() {
    const dateSaisie = new Date(this.value);
    const today = new Date();
    today.setHours(0,0,0,0);

    const msg = document.getElementById("publication_date_error");

    if (dateSaisie <= today) {
        msg.style.color = "red";
        msg.innerText = "❌ La date doit être ultérieure à aujourd'hui";
    } else {
        msg.style.color = "green";
        msg.innerText = "✅ Date valide";
    }
});

// Langue sélectionnée
document.querySelectorAll('input[name="language"]').forEach(radio => {
    radio.addEventListener("change", function() {
        const msg = document.getElementById("language_error");
        if (this.value === "AN") {
            msg.innerText = "🇬🇧 Livre en Anglais sélectionné";
            msg.style.color = "blue";
        } else if (this.value === "FR") {
            msg.innerText = "🇫🇷 Livre en Français sélectionné";
            msg.style.color = "green";
        }
    });
});

// Validation finale au submit
form.addEventListener("submit", function(event) {
    event.preventDefault();
    alert("Livre ajouté avec succès ! 🎉");
});
*/