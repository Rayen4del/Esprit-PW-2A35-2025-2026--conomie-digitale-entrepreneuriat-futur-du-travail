<?php
include_once(__DIR__ . '/../config.php');
include_once(__DIR__ . '/../Model/Produit.php');

class ProduitController {

    public function listProduits() {
        $sql = "SELECT * FROM produit";
        $db = config::getConnexion();

        try {
            $list = $db->query($sql);
            return $list;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function getProduitCountBySponsoring($id_sp) {
        $db = config::getConnexion();
        $query = $db->prepare("SELECT COUNT(*) FROM produit WHERE id_sp = :id_sp");
        $query->execute(['id_sp' => $id_sp]);
        return (int) $query->fetchColumn();
    }

    public function deleteProduitsBySponsoring($id_sp) {
        $sql = "DELETE FROM produit WHERE id_sp = :id_sp";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id_sp', $id_sp);

        try {
            $req->execute();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    private function getNextProduitId(): int {
        $db = config::getConnexion();
        try {
            $query = $db->query("SELECT MAX(id_p) AS max_id FROM produit");
            $maxId = $query->fetchColumn();
            return ($maxId === false || $maxId === null) ? 0 : intval($maxId) + 1;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function deleteProduit($id_p) {
        $sql = "DELETE FROM produit WHERE id_p = :id_p";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id_p', $id_p);

        try {
            $req->execute();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function addProduit(Produit $produit) {
        $id_p = $this->getNextProduitId();
        $sql = "INSERT INTO produit (id_p, nom, categrie, prix, description, image, id_sp) 
                VALUES (:id_p, :nom, :categrie, :prix, :description, :image, :id_sp)";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id_p' => $id_p,
                'nom' => $produit->getNom(),
                'categrie' => $produit->getCategrie(),
                'prix' => $produit->getPrix(),
                'description' => $produit->getDescription(),
                'image' => $produit->getImage(),
                'id_sp' => $produit->getIdSp()
            ]);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    public function updateProduit(Produit $produit, $id_p) {
        try {
            $db = config::getConnexion();
            $query = $db->prepare(
                'UPDATE produit SET
                    nom = :nom,
                    categrie = :categrie,
                    prix = :prix,
                    description = :description,
                    image = :image,
                    id_sp = :id_sp
                WHERE id_p = :id_p'
            );

            $query->execute([
                'id_p' => $id_p,
                'nom' => $produit->getNom(),
                'categrie' => $produit->getCategrie(),
                'prix' => $produit->getPrix(),
                'description' => $produit->getDescription(),
                'image' => $produit->getImage(),
                'id_sp' => $produit->getIdSp()
            ]);

        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function showProduit($id_p) {
        $sql = "SELECT * FROM produit WHERE id_p = :id_p";
        $db = config::getConnexion();
        $query = $db->prepare($sql);

        try {
            $query->execute([
                'id_p' => $id_p
            ]);

            $produit = $query->fetch();
            return $produit;

        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }
}
?>