<?php
require_once(__DIR__ . "/../model/Formation.php");

class FormationController
{
    private $model;

    public function __construct($db)
    {
        $this->model = new Formation($db);
    }

    // 📌 LIST ALL (front + backoffice)
    public function list()
    {
        return $this->model->getAll();
    }

    // 📌 GET ONE (edit mode)
    public function get($id)
    {
        return $this->model->getById($id);
    }

    // 📌 ADD FORMATION
    public function add($post, $files)
    {
        $titre = $post['titre'];
        $description = $post['description'];
        $domaine = $post['domaine'];
        $date = $post['date_creation'];
        $etat = $post['etat'];

        $image = $files['image']['name'];

        // upload folder (adapté à ton projet)
        $uploadDir = __DIR__ . "/../view/gestion_formation/html/uploads/";

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $newImage = time() . "_" . basename($image);
        move_uploaded_file($files['image']['tmp_name'], $uploadDir . $newImage);

        return $this->model->insert($titre, $description, $domaine, $date, $etat, $newImage);
    }

    // 📌 UPDATE FORMATION
    public function update($post)
    {
        $id = $post['id'];
        $titre = $post['titre'];
        $description = $post['description'];
        $domaine = $post['domaine'];
        $date = $post['date_creation'];
        $etat = $post['etat'];

        return $this->model->update($id, $titre, $description, $domaine, $date, $etat);
    }

    // 📌 DELETE FORMATION
    public function delete($id)
    {
        return $this->model->delete($id);
    }
}