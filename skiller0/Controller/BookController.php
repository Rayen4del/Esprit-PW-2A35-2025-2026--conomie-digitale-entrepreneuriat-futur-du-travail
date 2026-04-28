<?php
include(__DIR__ . '/../config.php');
include(__DIR__ . '/../Model/Book.php');

class BookController {

    public function listBooks() {
        $sql = "SELECT * FROM book";
        $db = config::getConnexion();
        try {
            $list = $db->query($sql);
            return $list;
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function deleteBook($id) {
        $sql = "DELETE FROM book WHERE id = :id";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id', $id);
        try {
            $req->execute();
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function addBook(Book $book) {
        $sql = "INSERT INTO book VALUES (NULL, :title, :author, :publicationDate, :langue, :status, :copies, :category)";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'title' => $book->getTitle(),
                'author' => $book->getAuthor(),
                'publicationDate' => $book->getPublicationDate() ? $book->getPublicationDate()->format('Y-m-d') : null,
                'langue' => $book->getLangue(),
                'status' => $book->getStatus(),
                'copies' => $book->getCopies(),
                'category' => $book->getCategory()
            ]);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    public function updateBook(Book $book, $id) {
        try {
            $db = config::getConnexion();
            $query = $db->prepare(
                'UPDATE book SET 
                    title = :title,
                    author = :author,
                    publicationDate = :publicationDate,
                    langue = :langue,
                    status = :status,
                    copies = :copies,
                    category = :category
                WHERE id = :id'
            );
            $query->execute([
                'id' => $id,
                'title' => $book->getTitle(),
                'author' => $book->getAuthor(),
                'publicationDate' => $book->getPublicationDate() ? $book->getPublicationDate()->format('Y-m-d') : null,
                'langue' => $book->getLangue(),
                'status' => $book->getStatus(),
                'copies' => $book->getCopies(),
                'category' => $book->getCategory()
            ]);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }


    public function showBook($id) {
        $sql="SELECT * FROM book WHERE id= $id";
        $db= config::getConnexion();
        $query= $db->prepare($sql);

        try
        {
            $query->execute();
            $book= $query->fetch();
            return $book;
        }
        catch(Exception $e)
        {
            die('Error: '. $e->getMessage());
        }
    }
   
}
?>