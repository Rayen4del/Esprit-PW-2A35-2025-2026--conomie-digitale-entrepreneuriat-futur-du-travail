<?php
require_once __DIR__ . "/../../Model/Book.php";
require_once __DIR__ . "/../../Controller/BookController.php";

$title = $_POST['title'];
$author = $_POST['author'];
$publicationDate = $_POST['publicationDate'];
$language = $_POST['language'];
$status = $_POST['status'];
$copies = $_POST['copies'];
$category = $_POST['category'];

$book1 = new Book($title, $author, $publicationDate, $language, $status, $copies, $category);

echo "<h2>Affichage avec var_dump :</h2>";
var_dump($book1);

$controller = new BookController();
$controller->showBook($book1);
?>