<?php
include '../../controller/BookController.php';
$bookC = new BookController();
$bookC->deleteBook($_GET["id"]);
header('Location: bookList.php');
?>


