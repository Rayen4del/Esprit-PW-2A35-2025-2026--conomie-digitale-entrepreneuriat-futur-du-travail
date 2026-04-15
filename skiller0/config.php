<?php
// Informations de connexion
$host = "localhost";
$user = "root";
$password = ""; // vide par défaut dans XAMPP
$dbname = "skiller_db"; 

// Connexion avec MySQL
$conn = new mysqli($host, $user, $password, $dbname);

// Vérification de la connexion
if ($conn->connect_error) {
    die("❌ Connexion échouée : " . $conn->connect_error);
}

// Message optionnel
// echo "✅ Connexion réussie !";
?>