<?php
require_once __DIR__ . "/../../Model/Book.php";
require_once __DIR__ . "/../../Controller/BookController.php";

$book1 = new Book(
    "Clean Code",
    "Robert C. Martin",
    "2008-08-01",
    "AN",
    "Disponible",
    5,
    "Technology"
);

echo "<h2>Affichage avec var_dump :</h2>";
var_dump($book1);

$controller = new BookController();
$controller->showBook($book1);
?>