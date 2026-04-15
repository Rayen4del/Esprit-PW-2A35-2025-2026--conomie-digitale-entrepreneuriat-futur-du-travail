<?php
require_once(__DIR__ . "/../config.php");

class Formation
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAll()
    {
        return $this->conn->query("SELECT * FROM formation ORDER BY ID_formation DESC");
    }

    public function getById($id)
    {
        return $this->conn->query("SELECT * FROM formation WHERE ID_formation=$id")->fetch_assoc();
    }

    public function insert($titre, $description, $domaine, $date, $etat, $image)
    {
        return $this->conn->query("INSERT INTO formation 
        (titre, description, domaine, date_creation, etat, image)
        VALUES 
        ('$titre','$description','$domaine','$date','$etat','$image')");
    }

    public function update($id, $titre, $description, $domaine, $date, $etat)
    {
        return $this->conn->query("UPDATE formation SET 
        titre='$titre',
        description='$description',
        domaine='$domaine',
        date_creation='$date',
        etat='$etat'
        WHERE ID_formation=$id");
    }

    public function delete($id)
    {
        return $this->conn->query("DELETE FROM formation WHERE ID_formation=$id");
    }
}